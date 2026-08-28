<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\EnrolledStudentResource;
use App\Models\CampusEnrollment;
use App\Models\CampusTeacher;
use Illuminate\Http\Request;

class TeacherCourseController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user();
        abort_unless($teacher instanceof CampusTeacher, 403);

        $courses = $teacher->courses()
            ->with('season', 'category')
            ->withCount(['enrollments as students_count' => fn ($q) => $q->whereIn('status', ['paid', 'pending'])])
            ->orderByDesc('start_date')
            ->get()
            ->each(fn ($c) => $c->sessions_past = $c->sessionsPast());

        return CourseResource::collection($courses);
    }

    public function show(Request $request, string $slug)
    {
        $teacher = $request->user();
        abort_unless($teacher instanceof CampusTeacher, 403);

        $course = $teacher->courses()
            ->with('season', 'category')
            ->where('campus_courses.slug', $slug)
            ->firstOrFail();

        $course->sessions_past = $course->sessionsPast();

        $students = CampusEnrollment::with('student')
            ->where('course_id', $course->id)
            ->whereIn('status', ['paid', 'pending'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return response()->json([
            'course'   => new CourseResource($course),
            'students' => EnrolledStudentResource::collection($students),
        ]);
    }
}
