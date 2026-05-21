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

        // Collect accessible course IDs (direct + parent templates)
        $courseIds = $enrollments->pluck('course_id');

        // Load documents accessible to this student for all enrolled courses
        $documentsByCourse = CampusDocument::where('status', 'active')
            ->where(function ($q) use ($courseIds) {
                $q->whereIn('course_id', $courseIds)
                  ->where(function ($q2) {
                      $q2->where('visibility', 'public')
                         ->orWhere('visibility', 'enrolled');
                  });
            })
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get()
            ->groupBy('course_id');

        return view('campus.portal.courses', compact('enrollments', 'documentsByCourse'));
    }
}
