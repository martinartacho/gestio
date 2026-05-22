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
        Schema::table('lms_lessons', function (Blueprint $table) {
            // Array de preguntes independents dels blocs de visualització
            // Tipus: open_text, select_from_examples, choice_one, choice_many, yes_no
            $table->json('questions')->nullable()->after('exercise');
        });
    }

    public function down(): void
    {
        Schema::table('lms_lessons', function (Blueprint $table) {
            $table->dropColumn('questions');
        });
    }
};
