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
        Schema::create('lms_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('campus_courses')->cascadeOnDelete();

            // Capçalera
            $table->unsignedSmallInteger('session_number');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('duration')->nullable();

            // Introducció
            $table->text('quote_text')->nullable();
            $table->string('quote_author')->nullable();
            $table->string('quote_work')->nullable();
            $table->text('intro_text')->nullable();

            // El tema d'avui
            $table->text('topic_text')->nullable();

            // Blocs JSON
            $table->json('concepts')->nullable();             // [{icon, title, description}]
            $table->json('text_cards')->nullable();           // [{type, title, author, year, extract, analysis}]
            $table->json('comparison')->nullable();           // {left_label, right_label, left_points[], right_points[]}
            $table->json('reflection_questions')->nullable(); // [{question}]
            $table->json('exercise')->nullable();             // {title, duration, statement, examples[], tips[], demo_first_person, demo_third_person}

            // Control
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['course_id', 'session_number']);
            $table->index(['course_id', 'status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_lessons');
    }
};
