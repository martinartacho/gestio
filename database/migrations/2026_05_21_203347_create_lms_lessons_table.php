<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('campus_courses')->cascadeOnDelete();
            $table->unsignedSmallInteger('session_number');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('duration')->nullable();
            $table->text('quote_text')->nullable();
            $table->string('quote_author')->nullable();
            $table->string('quote_work')->nullable();
            $table->text('intro_text')->nullable();
            $table->text('topic_text')->nullable();
            $table->json('concepts')->nullable();
            $table->json('text_cards')->nullable();
            $table->json('comparison')->nullable();
            $table->json('reflection_questions')->nullable();
            $table->json('exercise')->nullable();
            $table->json('questions')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['course_id', 'session_number']);
            $table->index(['course_id', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_lessons');
    }
};
