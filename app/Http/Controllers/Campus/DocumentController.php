<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusDocument;
use App\Models\CampusEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Descàrrega segura d'un document.
     * Comprova que l'usuari (alumne o professor) té accés.
     */
    public function download(Request $request, CampusDocument $document): StreamedResponse
    {
        abort_if(! $document->isFile() || ! $document->file_path, 404);
        abort_if(! Storage::disk('local')->exists($document->file_path), 404);

        // Comprovar accés
        $this->authorize($document);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->file_name ?? basename($document->file_path)
        );
    }

    private function authorize(CampusDocument $document): void
    {
        if ($document->visibility === 'public') {
            return; // Tothom pot accedir
        }

        $student = auth('student')->user();
        $teacher = auth('teacher')->user();
        $admin   = auth('web')->user();

        if ($admin) return; // Admin pot descarregar sempre

        if ($document->visibility === 'private') {
            // Només el professor propietari
            abort_unless(
                $teacher && $teacher->id === $document->teacher_id,
                403
            );
            return;
        }

        // visibility = 'enrolled': alumne matriculat o professor del curs
        if ($teacher) {
            abort_unless(
                $teacher->courses()->where('campus_courses.id', $document->course_id)->exists()
                || $teacher->id === $document->teacher_id,
                403
            );
            return;
        }

        if ($student && $document->course_id) {
            // Comprova matrícula directa o al curs pare (herència)
            $courseIds = collect([$document->course_id]);
            if ($document->course?->parent_id) {
                $courseIds->push($document->course->parent_id);
            }
            // Afegir cursos fills si el document és template
            if ($document->inherit_to_editions) {
                $childIds = \App\Models\CampusCourse::where('parent_id', $document->course_id)
                    ->pluck('id');
                $courseIds = $courseIds->merge($childIds);
            }

            $enrolled = CampusEnrollment::where('student_id', $student->id)
                ->whereIn('course_id', $courseIds)
                ->whereIn('status', ['paid', 'pending'])
                ->exists();

            abort_unless($enrolled, 403);
            return;
        }

        abort(403);
    }
}
