<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;

class PortalController extends Controller
{
    public function courses()
    {
        $student = auth('student')->user();

        $enrollments = $student->enrollments()
            ->with('course.category', 'course.season')
            ->orderByDesc('created_at')
            ->get();

        return view('campus.portal.courses', compact('enrollments'));
    }
}
