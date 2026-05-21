<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\LmsLesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LmsPreviewController extends Controller
{
    public function show(LmsLesson $lesson): View
    {
        abort_unless(
            Auth::user()?->can('courses.view') || Auth::user()?->hasRole('admin'),
            403
        );

        $lesson->loadMissing('course');
        $course       = $lesson->course;
        $lessons      = $course->lessons()->ordered()->get();
        $index        = $lessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson   = $index > 0 ? $lessons[$index - 1] : null;
        $nextLesson   = isset($lessons[$index + 1]) ? $lessons[$index + 1] : null;
        $totalLessons = $lessons->count();
        $isCompleted  = false;
        $isPreview    = true;

        return view('campus.lms.lesson', compact(
            'course', 'lesson', 'isCompleted', 'isPreview',
            'prevLesson', 'nextLesson', 'totalLessons'
        ));
    }
}
