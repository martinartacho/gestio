<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un professor pot pertànyer a més d'una institució (mateix canvi que
     * campus_students ahir). A diferència d'alumnes, aquí ja hi havia
     * professors duplicats (mateixa persona, una fila per tenant, mateix
     * email — no hi havia cap restricció que ho impedís) — cal fusionar-los
     * en una sola fila abans de poder fer l'email únic globalment, sense
     * perdre a quins tenants pertanyien.
     */
    public function up(): void
    {
        Schema::create('campus_teacher_tenant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('campus_teachers')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_id', 'tenant_id']);
        });

        $duplicateEmails = DB::table('campus_teachers')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('count(*) > 1')
            ->pluck('email');

        foreach ($duplicateEmails as $email) {
            $rows      = DB::table('campus_teachers')->where('email', $email)->orderBy('id')->get();
            $survivor  = $rows->first();
            $duplicates = $rows->skip(1);

            foreach ($rows as $row) {
                DB::table('campus_teacher_tenant')->insertOrIgnore([
                    'teacher_id' => $survivor->id,
                    'tenant_id'  => $row->tenant_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($duplicates as $dup) {
                DB::table('campus_course_teacher')->where('teacher_id', $dup->id)->update(['teacher_id' => $survivor->id]);
                DB::table('campus_teacher_payments')->where('teacher_id', $dup->id)->update(['teacher_id' => $survivor->id]);
                DB::table('campus_documents')->where('teacher_id', $dup->id)->update(['teacher_id' => $survivor->id]);
                DB::table('campus_teachers')->where('id', $dup->id)->delete();
            }
        }

        // Backfill de la resta (els que no tenien cap duplicat).
        DB::table('campus_teachers')
            ->whereNotNull('tenant_id')
            ->select('id', 'tenant_id')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                DB::table('campus_teacher_tenant')->insertOrIgnore([
                    'teacher_id' => $row->id,
                    'tenant_id'  => $row->tenant_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('campus_teachers', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique('campus_teachers_tenant_id_code_unique');
            $table->dropColumn('tenant_id');
            // code i email tornen a ser únics globalment: una persona, una fila.
            $table->unique('code');
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campus_teachers', function (Blueprint $table) {
            $table->dropUnique('campus_teachers_code_unique');
            $table->dropUnique('campus_teachers_email_unique');
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('campus_teachers')->orderBy('id')->get()->each(function ($teacher) {
            $firstTenantId = DB::table('campus_teacher_tenant')
                ->where('teacher_id', $teacher->id)
                ->value('tenant_id');

            if ($firstTenantId) {
                DB::table('campus_teachers')->where('id', $teacher->id)->update(['tenant_id' => $firstTenantId]);
            }
        });

        Schema::table('campus_teachers', function (Blueprint $table) {
            $table->unique(['tenant_id', 'code']);
        });

        Schema::dropIfExists('campus_teacher_tenant');
    }
};
