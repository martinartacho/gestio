<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Taules de negoci que passen a pertànyer a un tenant concret. */
    private const TABLES = [
        'campus_seasons',
        'campus_categories',
        'campus_spaces',
        'campus_time_slots',
        'campus_teachers',
        'campus_courses',
        'campus_students',
        'campus_enrollments',
        'campus_payments',
        'campus_teacher_payments',
        'campus_documents',
        'campus_news',
        'campus_holidays',
        'associat_members',
        'associat_sepa_remittances',
        'associat_quotes',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                // Nullable: permet aplicar la migració sense trencar files
                // existents; es reomplen totes amb el tenant "Campus" just
                // després (veure nota al README/memòria del projecte).
                $blueprint->foreignId('tenant_id')->nullable()->after('id')
                    ->constrained()->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};
