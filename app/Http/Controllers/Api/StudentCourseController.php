<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentEnrollmentResource;
use App\Models\CampusStudent;
use Illuminate\Http\Request;

class StudentCourseController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();
        abort_unless($student instanceof CampusStudent, 403);

        $enrollments = $student->enrollments()
            ->with(['course' => fn ($q) => $q->with('category', 'season')])
            ->whereIn('status', ['paid', 'pending'])
            ->orderByDesc('created_at')
            ->get()
            ->each(function ($enrollment) {
                if ($enrollment->course) {
                    $enrollment->course->sessions_past = $enrollment->course->sessionsPast();
                }
            });

        return StudentEnrollmentResource::collection($enrollments);
    }
}
