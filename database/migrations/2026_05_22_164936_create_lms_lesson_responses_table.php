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
        Schema::create('lms_lesson_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lms_lessons')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('campus_students')->cascadeOnDelete();
            $table->unsignedSmallInteger('question_index');          // índex a questions[]
            $table->string('question_type', 30);                     // snapshot del tipus
            $table->text('response_text')->nullable();               // open_text, select_from_examples
            $table->json('response_choices')->nullable();            // choice_one, choice_many
            $table->tinyInteger('response_bool')->nullable();        // yes_no (0/1)
            $table->decimal('score', 4, 2)->nullable();              // null = no avaluable
            $table->boolean('auto_graded')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['lesson_id', 'student_id', 'question_index']);
            $table->index(['student_id', 'lesson_id']);
            $table->index(['lesson_id', 'question_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_lesson_responses');
    }
};
