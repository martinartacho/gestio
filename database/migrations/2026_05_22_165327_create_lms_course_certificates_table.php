<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lms_course_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('campus_courses')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('campus_students')->cascadeOnDelete();
            $table->string('verb', 20)->default('passed'); // xAPI: passed | failed
            $table->timestamp('issued_at');
            $table->string('certificate_number', 30)->unique();
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_course_certificates');
    }
};
