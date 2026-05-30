<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('associat_members', function (Blueprint $table) {
            $table->enum('mandate_method', ['paper', 'electronic', 'web'])
                  ->nullable()
                  ->after('mandate_sequence');
            $table->string('mandate_document')->nullable()->after('mandate_method');
            $table->string('mandate_ip', 45)->nullable()->after('mandate_document');
        });
    }

    public function down(): void
    {
        Schema::table('associat_members', function (Blueprint $table) {
            $table->dropColumn(['mandate_method', 'mandate_document', 'mandate_ip']);
        });
    }
};
