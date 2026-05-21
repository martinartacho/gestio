<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusDocument;

class PortalController extends Controller
{
    public function courses()
    {
        $student = auth('student')->user();

        $enrollments = $student->enrollments()
            ->with('course.category', 'course.season')
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

        return view('campus.portal.courses', compact('enrollments', 'documentsByCourse'));
    }
}
