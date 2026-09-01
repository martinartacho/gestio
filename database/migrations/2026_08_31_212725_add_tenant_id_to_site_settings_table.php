<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La configuració del lloc (marca, colors, interruptors de mòduls...)
     * era una sola fila per clau, compartida per tots els tenants — per
     * això "Campus" i "Tenant Test" es veien idèntics. Cada tenant passa
     * a tenir la seva pròpia fila per clau; les que ja existien eren
     * configuració real de "Campus", no un valor genèric, així que hi
     * queden assignades (un tenant nou simplement no en té cap encara,
     * i cada crida a setting($key, $default) ja porta el seu propi valor
     * per defecte al codi).
     *
     * 'key' era la PK — cal canviar-ho a una PK normal + unique(tenant_id,
     * key) per poder-hi tenir una fila per tenant amb la mateixa clau.
     * Es recrea la taula en lloc de fer ALTER TABLE perquè SQLite (usat
     * als tests) no permet afegir una columna PRIMARY KEY amb ALTER.
     */
    public function up(): void
    {
        $existing = DB::table('site_settings')->get();
        $campusId = DB::table('tenants')->where('slug', 'campus')->value('id');

        Schema::dropIfExists('site_settings');

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'key']);
        });

        if ($campusId) {
            $now = now();
            $rows = $existing->map(fn ($row) => [
                'tenant_id'  => $campusId,
                'key'        => $row->key,
                'value'      => $row->value,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->updated_at ?? $now,
            ])->all();

            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('site_settings')->insert($chunk);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $existing = DB::table('site_settings')->get();

        Schema::dropIfExists('site_settings');

        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        $now  = now();
        $rows = $existing->unique('key')->map(fn ($row) => [
            'key'        => $row->key,
            'value'      => $row->value,
            'created_at' => $row->created_at ?? $now,
            'updated_at' => $row->updated_at ?? $now,
        ])->values()->all();

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('site_settings')->insert($chunk);
        }
    }
};
