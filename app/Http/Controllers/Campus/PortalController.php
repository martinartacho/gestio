<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusDocument;
use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function courses()
    {
        $student = auth('student')->user();

        // Un alumne pot pertànyer a més d'una institució — aquesta llista
        // ajunta els cursos de totes, cal indicar de quina és cada un.
        $enrollments = $student->enrollments()
            ->with([
                'course' => fn ($q) => $q
                    ->with('category', 'season', 'tenant')
                    ->withCount(['lessons as published_lessons_count' => fn ($q) => $q->where('status', 'published')]),
            ])
            ->whereIn('status', ['paid', 'pending'])
            ->orderByDesc('created_at')
            ->get();

        $courseIds = $enrollments->pluck('course_id');

        // Càrrega candidats: actius, no privats, del curs correcte
        // El filtre de session_number es fa en PHP (requereix calcular sessions passades)
        $candidates = CampusDocument::where('status', 'active')
            ->whereIn('course_id', $courseIds)
            ->whereIn('visibility', ['public', 'enrolled'])
            ->with('course')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        // Filtre OR: available_from O session_number (lògica idèntica a DocumentController)
        $now = now();
        $documentsByCourse = $candidates
            ->filter(function (CampusDocument $doc) use ($now) {
                $conditions = [];

                if ($doc->available_from) {
                    $conditions[] = $now->gte($doc->available_from);
                }

                if ($doc->session_number && $doc->course) {
                    $done        = $doc->course->sessionsPast() ?? 0;
                    $conditions[] = $done >= $doc->session_number;
                }

                return empty($conditions) || in_array(true, $conditions, true);
            })
            ->groupBy('course_id');

        // Nomes cal mostrar la insígnia d'institució si en té més d'una —
        // per a la immensa majoria (una sola) seria soroll visual.
        $showInstitution = $student->tenants()->count() > 1;

        return view('campus.portal.courses', compact('enrollments', 'documentsByCourse', 'showInstitution'));
    }

    public function editProfile(): View
    {
        return view('campus.portal.profile', ['student' => auth('student')->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone'    => ['nullable', 'string', 'max:20'],
            'locale'   => Locales::rule(),
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $student = auth('student')->user();
        $student->update($data);

        // El missatge de confirmació ja en el nou idioma.
        app()->setLocale($student->locale ?? app()->getLocale());

        return back()->with('success', __('site.profile_updated'));
    }
}
