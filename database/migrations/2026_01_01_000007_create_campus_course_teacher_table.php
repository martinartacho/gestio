<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_course_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                  ->constrained('campus_courses')
                  ->onDelete('cascade');
            $table->foreignId('teacher_id')
                  ->constrained('campus_teachers')
                  ->onDelete('cascade');
            $table->enum('role', ['main', 'assistant'])->default('main');
            $table->decimal('sessions_assigned', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'teacher_id']);
            $table->index(['course_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_course_teacher');
    }
};
