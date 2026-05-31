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
            $table->string('code')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('campus_courses')->nullOnDelete();
            $table->foreignId('season_id')->constrained('campus_seasons')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('campus_categories')->nullOnDelete();
            $table->foreignId('space_id')->nullable()->constrained('campus_spaces')->nullOnDelete();
            $table->foreignId('time_slot_id')->nullable()->constrained('campus_time_slots')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('calendar_notes')->nullable();
            $table->unsignedSmallInteger('sessions')->nullable();
            $table->unsignedSmallInteger('hours')->nullable();
            $table->enum('format', ['presencial', 'online', 'semipresencial', 'hibrid'])->default('presencial');
            $table->unsignedSmallInteger('max_students')->nullable();
            $table->decimal('price', 8, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('requirements')->nullable();
            $table->enum('status', ['draft', 'planning', 'active', 'closed'])->default('draft');
            $table->boolean('is_public')->default(true);
            $table->boolean('open_enrollment')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['season_id', 'status']);
            $table->index('category_id', 'campus_courses_category_id_plain_index');
            $table->index(['category_id', 'status']);
            $table->index('parent_id');
            $table->index('format');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_courses');
    }
};
