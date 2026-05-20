<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. campus_seasons: afegir status, eliminar is_active
        Schema::table('campus_seasons', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('is_active');
        });

        // Migrar dades: is_active=true → active, false → draft o closed per dates
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE campus_seasons SET status = CASE
                    WHEN is_active = 1 THEN 'active'
                    WHEN end_date < CURDATE() THEN 'closed'
                    ELSE 'draft'
                END
            ");
        } else {
            // SQLite
            DB::statement("
                UPDATE campus_seasons SET status = CASE
                    WHEN is_active = 1 THEN 'active'
                    WHEN end_date < date('now') THEN 'closed'
                    ELSE 'draft'
                END
            ");
        }

        Schema::table('campus_seasons', function (Blueprint $table) {
            $table->dropIndex('campus_seasons_is_active_index');
            $table->dropColumn('is_active');
        });

        // 2. campus_courses: eliminar is_active
        Schema::table('campus_courses', function (Blueprint $table) {
            $table->dropIndex('campus_courses_category_id_is_active_index');
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('campus_seasons', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('quadrimester');
        });

        DB::statement("UPDATE campus_seasons SET is_active = CASE WHEN status = 'active' THEN 1 ELSE 0 END");

        Schema::table('campus_seasons', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('campus_courses', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('status');
        });

        DB::statement("UPDATE campus_courses SET is_active = CASE WHEN status = 'active' THEN 1 ELSE 0 END");
    }
};
