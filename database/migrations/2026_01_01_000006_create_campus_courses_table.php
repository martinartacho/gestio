<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_courses', function (Blueprint $table) {
            $table->id();

            // ── Identificació ─────────────────────────────────────────────
            $table->string('code')->nullable();              // PAPIRO-001
            $table->string('title');
            $table->string('slug')->unique();

            // ── Jerarquia (null = plantilla, int = edició) ────────────────
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('campus_courses')
                  ->onDelete('set null');

            // ── Relacions catàleg ─────────────────────────────────────────
            $table->foreignId('season_id')
                  ->constrained('campus_seasons')
                  ->onDelete('restrict');
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('campus_categories')
                  ->onDelete('set null');
            $table->foreignId('space_id')
                  ->nullable()
                  ->constrained('campus_spaces')
                  ->onDelete('set null');
            $table->foreignId('time_slot_id')
                  ->nullable()
                  ->constrained('campus_time_slots')
                  ->onDelete('set null');

            // ── Planificació ──────────────────────────────────────────────
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('calendar_notes')->nullable();      // "16/2, 23/2, 2/3..."
            $table->unsignedSmallInteger('sessions')->nullable();
            $table->unsignedSmallInteger('hours')->nullable();

            // ── Format i places ───────────────────────────────────────────
            $table->enum('format', [
                'presencial',
                'online',
                'semipresencial',
                'hibrid',
            ])->default('presencial');
            $table->unsignedSmallInteger('max_students')->nullable(); // null = sense límit

            // ── Economia ──────────────────────────────────────────────────
            $table->decimal('price', 8, 2)->default(0.00);

            // ── Contingut ─────────────────────────────────────────────────
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('requirements')->nullable();

            // ── Estat ─────────────────────────────────────────────────────
            $table->enum('status', [
                'draft',
                'planning',
                'active',
                'closed',
            ])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);

            // ── Metadades ─────────────────────────────────────────────────
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamps();

            // ── Índexos ───────────────────────────────────────────────────
            $table->index(['season_id', 'status']);
            $table->index(['category_id', 'is_active']);
            $table->index(['parent_id']);
            $table->index(['format']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_courses');
    }
};
