<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fins ara, crear el tenant "Campus" i assignar-hi les dades existents
     * es feia a mà per tinker en cada entorn (local) — no reproduïble en
     * desplegar a un servidor nou (p. ex. demo.artacho.org), on ningú ho
     * hauria fet manualment i totes les columnes tenant_id quedarien buides.
     * Aquesta migració ho fa de manera reproduïble: si no existeix cap
     * tenant "campus", el crea, i assigna a totes les dades ja existents
     * (sense tenant_id encara) que abans de la multi-tenantització eren,
     * de fet, l'única entitat de la instal·lació.
     *
     * Posició important a la cadena: ha d'anar després d'afegir tenant_id
     * a users/taules de negoci/cua, i abans de convertir alumnat, professorat
     * i socis a relació N:M (aquelles migracions llegeixen el tenant_id
     * d'aquesta per fer el backfill del pivot).
     */
    private const TABLES = [
        'campus_seasons', 'campus_categories', 'campus_spaces', 'campus_time_slots',
        'campus_teachers', 'campus_courses', 'campus_students', 'campus_enrollments',
        'campus_payments', 'campus_teacher_payments', 'campus_documents', 'campus_news',
        'campus_holidays', 'associat_members', 'associat_sepa_remittances',
        'associat_quotes', 'campus_queue_entries',
    ];

    public function up(): void
    {
        $tenantId = DB::table('tenants')->where('slug', 'campus')->value('id');

        if (! $tenantId) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name'       => 'Campus',
                'slug'       => 'campus',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);

        foreach (self::TABLES as $table) {
            if (DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
                DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
            }
        }
    }

    /**
     * Reverse the migrations. Best-effort: torna tenant_id a NULL allà on
     * hi hagi columna. No esborra el tenant "campus" (pot tenir dades
     * creades després que aquesta migració no sap desfer amb seguretat).
     */
    public function down(): void
    {
        $tenantId = DB::table('tenants')->where('slug', 'campus')->value('id');

        if (! $tenantId) {
            return;
        }

        DB::table('users')->where('tenant_id', $tenantId)->update(['tenant_id' => null]);

        foreach (self::TABLES as $table) {
            if (DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
                DB::table($table)->where('tenant_id', $tenantId)->update(['tenant_id' => null]);
            }
        }
    }
};
