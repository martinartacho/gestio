<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StudentCourseController;
use App\Http\Controllers\Api\TeacherCourseController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::get('/available-roles', [AuthController::class, 'availableRoles']);
    Route::post('/switch-role', [AuthController::class, 'switchRole']);

    Route::get('/student/courses', [StudentCourseController::class, 'index']);

    Route::get('/teacher/courses', [TeacherCourseController::class, 'index']);
    Route::get('/teacher/courses/{slug}', [TeacherCourseController::class, 'show']);

    Route::get('/member/profile', [MemberController::class, 'profile']);
});
