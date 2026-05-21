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
        Schema::create('campus_documents', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            // Tipus: fitxer pujat o URL externa
            $table->enum('type', ['file', 'url'])->default('file');

            // Fitxer pujat
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->string('mime_type')->nullable();

            // URL externa (YouTube, Drive…)
            $table->string('url', 2048)->nullable();

            // Relacions
            $table->foreignId('course_id')
                  ->nullable()
                  ->constrained('campus_courses')
                  ->nullOnDelete();
            $table->foreignId('teacher_id')
                  ->nullable()
                  ->constrained('campus_teachers')
                  ->nullOnDelete();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Visibilitat
            $table->enum('visibility', ['public', 'enrolled', 'private'])->default('enrolled');

            // Herència a cursos fills (si el curs és template)
            $table->boolean('inherit_to_editions')->default(false);

            // Condicions d'accés opcionals
            $table->timestamp('available_from')->nullable();
            $table->unsignedSmallInteger('session_number')->nullable();

            // Ordre i estat
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'draft'])->default('active');

            $table->timestamps();

            $table->index(['course_id', 'status', 'visibility']);
            $table->index(['teacher_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_documents');
    }
};
