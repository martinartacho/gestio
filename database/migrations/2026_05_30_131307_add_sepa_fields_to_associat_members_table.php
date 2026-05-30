<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('associat_members', function (Blueprint $table) {
            // Dades bancàries per a domiciliació SEPA
            $table->text('bank_iban')->nullable()->after('data_consent');
            $table->string('bank_holder')->nullable()->after('bank_iban');
            $table->string('mandate_reference')->nullable()->unique()->after('bank_holder');
            $table->date('mandate_signed_at')->nullable()->after('mandate_reference');
            $table->enum('mandate_sequence', ['FRST', 'RCUR'])->nullable()->after('mandate_signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('associat_members', function (Blueprint $table) {
            $table->dropColumn([
                'bank_iban', 'bank_holder',
                'mandate_reference', 'mandate_signed_at', 'mandate_sequence',
            ]);
        });
    }
};
