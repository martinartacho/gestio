<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un soci pot pertànyer a més d'una institució (mateix canvi que
     * campus_students i campus_teachers). Sense duplicats existents aquí
     * (a diferència de campus_teachers), backfill directe.
     */
    public function up(): void
    {
        Schema::create('associat_member_tenant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('associat_members')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['member_id', 'tenant_id']);
        });

        DB::table('associat_members')
            ->whereNotNull('tenant_id')
            ->select('id', 'tenant_id')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                DB::table('associat_member_tenant')->insert([
                    'member_id'  => $row->id,
                    'tenant_id'  => $row->tenant_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('associat_members', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('associat_members_tenant_id_member_number_unique');
            $table->dropUnique('associat_members_tenant_id_email_unique');
            $table->dropUnique('associat_members_tenant_id_mandate_reference_unique');
            $table->dropColumn('tenant_id');
            // Únics globalment altre cop: una persona, una fila.
            $table->unique('member_number');
            $table->unique('email');
            $table->unique('mandate_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('associat_members', function (Blueprint $table) {
            $table->dropUnique('associat_members_member_number_unique');
            $table->dropUnique('associat_members_email_unique');
            $table->dropUnique('associat_members_mandate_reference_unique');
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('associat_members')->orderBy('id')->get()->each(function ($member) {
            $firstTenantId = DB::table('associat_member_tenant')
                ->where('member_id', $member->id)
                ->value('tenant_id');

            if ($firstTenantId) {
                DB::table('associat_members')->where('id', $member->id)->update(['tenant_id' => $firstTenantId]);
            }
        });

        Schema::table('associat_members', function (Blueprint $table) {
            $table->unique(['tenant_id', 'member_number']);
            $table->unique(['tenant_id', 'email']);
            $table->unique(['tenant_id', 'mandate_reference']);
        });

        Schema::dropIfExists('associat_member_tenant');
    }
};
