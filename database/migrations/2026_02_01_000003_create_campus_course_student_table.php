<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_course_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('campus_courses')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('campus_students')->onDelete('cascade');
            $table->foreignId('enrollment_id')->constrained('campus_enrollments')->onDelete('cascade');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_course_student');
    }
};
