<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Restriccions unique que eren globals i haurien de ser per tenant.
     * Trobat en real: crear una segona temporada "2026-1" a un segon
     * tenant petava, perquè el primer tenant ja en tenia una amb el
     * mateix any+quadrimestre. users.email es queda global a propòsit:
     * el login de l'admin és previ a saber el tenant.
     */
    public function up(): void
    {
        Schema::table('campus_seasons', function (Blueprint $table) {
            $table->dropUnique('campus_seasons_year_quadrimester_unique');
            $table->unique(['tenant_id', 'year', 'quadrimester']);
        });

        Schema::table('campus_spaces', function (Blueprint $table) {
            $table->dropUnique('campus_spaces_code_unique');
            $table->unique(['tenant_id', 'code']);
        });

        Schema::table('campus_time_slots', function (Blueprint $table) {
            $table->dropUnique('campus_time_slots_day_of_week_code_unique');
            $table->unique(['tenant_id', 'day_of_week', 'code']);
        });

        Schema::table('campus_teachers', function (Blueprint $table) {
            $table->dropUnique('campus_teachers_code_unique');
            $table->unique(['tenant_id', 'code']);
        });

        Schema::table('campus_students', function (Blueprint $table) {
            $table->dropUnique('campus_students_email_unique');
            $table->unique(['tenant_id', 'email']);
        });

        Schema::table('associat_members', function (Blueprint $table) {
            $table->dropUnique('associat_members_member_number_unique');
            $table->dropUnique('associat_members_email_unique');
            $table->dropUnique('associat_members_mandate_reference_unique');
            $table->unique(['tenant_id', 'member_number']);
            $table->unique(['tenant_id', 'email']);
            $table->unique(['tenant_id', 'mandate_reference']);
        });

        Schema::table('associat_sepa_remittances', function (Blueprint $table) {
            $table->dropUnique('associat_sepa_remittances_reference_unique');
            $table->unique(['tenant_id', 'reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campus_seasons', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'year', 'quadrimester']);
            $table->unique(['year', 'quadrimester']);
        });

        Schema::table('campus_spaces', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique('code');
        });

        Schema::table('campus_time_slots', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'day_of_week', 'code']);
            $table->unique(['day_of_week', 'code']);
        });

        Schema::table('campus_teachers', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique('code');
        });

        Schema::table('campus_students', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique('email');
        });

        Schema::table('associat_members', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'member_number']);
            $table->dropUnique(['tenant_id', 'email']);
            $table->dropUnique(['tenant_id', 'mandate_reference']);
            $table->unique('member_number');
            $table->unique('email');
            $table->unique('mandate_reference');
        });

        Schema::table('associat_sepa_remittances', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'reference']);
            $table->unique('reference');
        });
    }
};
