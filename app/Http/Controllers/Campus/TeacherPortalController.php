<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusDocument;
use App\Models\CampusEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class TeacherPortalController extends Controller
{
    public function courses()
    {
        $teacher = auth('teacher')->user();

        $courses = $teacher->courses()
            ->with('season', 'category', 'space', 'timeSlot')
            ->withCount(['enrollments as students_count' => fn($q) => $q->whereIn('status', ['paid', 'pending'])])
            ->orderByDesc('start_date')
            ->get()
            ->each(fn($c) => $c->sessions_past = $c->sessionsPast());

        return view('campus.teacher.portal.courses', compact('courses'));
    }

    public function course(string $slug)
    {
        $teacher = auth('teacher')->user();

        $course = $teacher->courses()
            ->with('season', 'category', 'space', 'timeSlot')
            ->where('campus_courses.slug', $slug)
            ->firstOrFail();

        $lessons = $course->lessons()->orderBy('sort_order')->get();

        $enrollments = CampusEnrollment::with('student')
            ->where('course_id', $course->id)
            ->whereIn('status', ['paid', 'pending'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Professor veu tots els documents actius (inclosos els no disponibles encara)
        // Carguem 'course' perquè not_available_reason pugui calcular sessionsPast()
        $documents = CampusDocument::where('course_id', $course->id)
            ->where('status', 'active')
            ->with('course')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return view('campus.teacher.portal.course', compact('course', 'enrollments', 'documents', 'lessons'));
    }

    public function uploadDocument(Request $request, string $slug)
    {
        $teacher = auth('teacher')->user();

        $course = $teacher->courses()
            ->where('campus_courses.slug', $slug)
            ->firstOrFail();

        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'type'       => ['required', 'in:file,url'],
            'file'       => ['required_if:type,file', 'file',
                             'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,mp4,mp3,zip',
                             'max:' . CampusDocument::MAX_FILE_SIZE_KB],
            'url'        => ['required_if:type,url', 'nullable', 'url', 'max:2048'],
            'visibility' => ['required', 'in:public,enrolled,private'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $doc = new CampusDocument([
            'title'       => $data['title'],
            'type'        => $data['type'],
            'visibility'  => $data['visibility'],
            'description' => $data['description'] ?? null,
            'course_id'   => $course->id,
            'teacher_id'  => $teacher->id,
            'created_by'  => null,
            'status'      => 'active',
        ]);

        if ($data['type'] === 'file' && $request->hasFile('file')) {
            $file      = $request->file('file');
            $path      = $file->store('campus/documents', 'local');
            $doc->file_path  = $path;
            $doc->file_name  = $file->getClientOriginalName();
            $doc->file_size  = $file->getSize();
            $doc->mime_type  = $file->getMimeType();
        } else {
            $doc->url = $data['url'];
        }

        $doc->save();

        return back()->with('success', 'Document pujat correctament.');
    }

    public function deleteDocument(Request $request, string $slug, CampusDocument $document)
    {
        $teacher = auth('teacher')->user();

        // Only the teacher owner or a teacher of this course can delete
        abort_unless(
            $document->teacher_id === $teacher->id
            || $teacher->courses()->where('campus_courses.id', $document->course_id)->exists(),
            403
        );

        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document eliminat.');
    }

    // ── Gestor global de documents ─────────────────────────────────────────────

    public function documents(Request $request)
    {
        $teacher = auth('teacher')->user();

        // Cursos del professor per al filtre
        $myCourses = $teacher->courses()->orderByDesc('start_date')->get();

        // Documents propis
        $ownQuery = CampusDocument::where('tenant_id', current_tenant()?->id)
            ->where('teacher_id', $teacher->id);

        // Documents d'altres professors visibles (public o enrolled, no privats)
        // — sense el filtre de tenant, es veurien documents d'altres entitats.
        $othersQuery = CampusDocument::where('tenant_id', current_tenant()?->id)
            ->whereNotNull('teacher_id')
            ->where('teacher_id', '!=', $teacher->id)
            ->whereIn('visibility', ['public', 'enrolled'])
            ->where('status', 'active');

        // Aplicar filtres de la URL
        foreach ([$ownQuery, $othersQuery] as $q) {
            if ($request->filled('course_id')) {
                $q->where('course_id', $request->course_id);
            }
            if ($request->filled('type')) {
                $q->where('type', $request->type);
            }
        }

        if ($request->filled('visibility')) {
            $ownQuery->where('visibility', $request->visibility);
            $othersQuery->where('visibility', $request->visibility);
        }

        $ownDocuments    = $ownQuery->with('course')->orderBy('sort_order')->orderByDesc('created_at')->get();
        $othersDocuments = $othersQuery->with('course', 'teacher')->orderByDesc('created_at')->get();

        return view('campus.teacher.portal.documents', compact(
            'ownDocuments', 'othersDocuments', 'myCourses'
        ));
    }

    public function editDocument(CampusDocument $document)
    {
        $teacher = auth('teacher')->user();
        abort_unless($document->teacher_id === $teacher->id, 403);

        $courses = $teacher->courses()->orderByDesc('start_date')->get();

        return view('campus.teacher.portal.document-edit', compact('document', 'courses'));
    }

    public function updateDocument(Request $request, CampusDocument $document)
    {
        $teacher = auth('teacher')->user();
        abort_unless($document->teacher_id === $teacher->id, 403);

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'visibility'     => ['required', 'in:public,enrolled,private'],
            'course_id'      => ['nullable', 'integer', 'exists:campus_courses,id'],
            'status'         => ['required', 'in:active,draft'],
            'url'            => ['nullable', 'url', 'max:2048', 'required_if:type,url'],
            'available_from' => ['nullable', 'date'],
            'session_number' => ['nullable', 'integer', 'min:1'],
            'sort_order'     => ['nullable', 'integer', 'min:0'],
            'file'           => ['nullable', 'file',
                                 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,webp,mp4,mp3,zip',
                                 'max:' . CampusDocument::MAX_FILE_SIZE_KB],
        ]);

        $document->title          = $data['title'];
        $document->description    = $data['description'] ?? null;
        $document->visibility     = $data['visibility'];
        $document->course_id      = $data['course_id'] ?? null;
        $document->status         = $data['status'];
        $document->available_from = $data['available_from'] ?? null;
        $document->session_number = $data['session_number'] ?? null;
        $document->sort_order     = $data['sort_order'] ?? 0;

        if ($document->type === 'url') {
            $document->url = $data['url'] ?? $document->url;
        }

        if ($document->type === 'file' && $request->hasFile('file')) {
            // Eliminar fitxer antic
            if ($document->file_path) {
                Storage::disk('local')->delete($document->file_path);
            }
            $file = $request->file('file');
            $document->file_path = $file->store('campus/documents', 'local');
            $document->file_name = $file->getClientOriginalName();
            $document->file_size = $file->getSize();
            $document->mime_type = $file->getMimeType();
        }

        $document->save();

        return redirect()->route('teacher.portal.documents')
                         ->with('success', 'Document actualitzat correctament.');
    }

    public function destroyDocument(CampusDocument $document)
    {
        $teacher = auth('teacher')->user();
        abort_unless($document->teacher_id === $teacher->id, 403);

        if ($document->file_path) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('teacher.portal.documents')
                         ->with('success', 'Document eliminat.');
    }

    public function editProfile()
    {
        $teacher = auth('teacher')->user();
        return view('campus.teacher.portal.profile', compact('teacher'));
    }

    public function updateProfile(Request $request)
    {
        $teacher = auth('teacher')->user();

        $data = $request->validate([
            'phone'    => ['nullable', 'string', 'max:20'],
            'bio'      => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $teacher->update($data);

        return back()->with('success', 'Perfil actualitzat correctament.');
    }

    public function liquidations()
    {
        $teacher = auth('teacher')->user();

        $payments = $teacher->teacherPayments()
            ->with('course.season')
            ->orderByDesc('created_at')
            ->get();

        return view('campus.teacher.portal.liquidations', compact('payments'));
    }
}
