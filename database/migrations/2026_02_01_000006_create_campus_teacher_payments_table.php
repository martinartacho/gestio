<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_teacher_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')
                  ->constrained('campus_seasons')
                  ->onDelete('restrict');
            $table->foreignId('teacher_id')
                  ->constrained('campus_teachers')
                  ->onDelete('restrict');
            $table->foreignId('course_id')
                  ->nullable()
                  ->constrained('campus_courses')
                  ->onDelete('set null');

            $table->string('sessions_description')->nullable();
            $table->unsignedSmallInteger('sessions_count')->default(0);
            $table->decimal('price_per_session', 8, 2)->default(0);
            $table->decimal('gross_amount', 10, 2)->default(0);
            $table->decimal('retention_pct', 5, 2)->default(0);
            $table->decimal('retention_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->boolean('issues_invoice')->default(false);

            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'season_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_teacher_payments');
    }
};
