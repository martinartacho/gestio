<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un alumne pot pertànyer a més d'una institució (com ja fa User amb
     * HasTenants) — passa de tenant_id directe a una relació N:M.
     */
    public function up(): void
    {
        Schema::create('campus_student_tenant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('campus_students')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'tenant_id']);
        });

        // Backfill: cada alumne guanya una fila pivot amb el seu tenant actual.
        DB::table('campus_students')
            ->whereNotNull('tenant_id')
            ->select('id', 'tenant_id')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                DB::table('campus_student_tenant')->insert([
                    'student_id' => $row->id,
                    'tenant_id'  => $row->tenant_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('campus_students', function (Blueprint $table) {
            // L'índex unique (tenant_id,email) és el que sustenta la FK a
            // MySQL — cal treure la FK abans de poder-lo esborrar.
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('campus_students_tenant_id_email_unique');
            $table->dropColumn('tenant_id');
            // email torna a ser únic globalment: el login ja no depèn del tenant.
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campus_students', function (Blueprint $table) {
            $table->dropUnique('campus_students_email_unique');
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('campus_students')->orderBy('id')->get()->each(function ($student) {
            $firstTenantId = DB::table('campus_student_tenant')
                ->where('student_id', $student->id)
                ->value('tenant_id');

            if ($firstTenantId) {
                DB::table('campus_students')->where('id', $student->id)->update(['tenant_id' => $firstTenantId]);
            }
        });

        Schema::table('campus_students', function (Blueprint $table) {
            $table->unique(['tenant_id', 'email']);
        });

        Schema::dropIfExists('campus_student_tenant');
    }
};
