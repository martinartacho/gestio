<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campus_enrollments', function (Blueprint $table) {
            // Referència única per identificar el pagament (transferència, Bizum, etc.)
            $table->string('payment_reference', 12)->nullable()->unique()->after('payment_method');
            // Data límit per completar el pagament manual (null = sense caducitat)
            $table->timestamp('payment_expires_at')->nullable()->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('campus_enrollments', function (Blueprint $table) {
            $table->dropUnique(['payment_reference']);
            $table->dropColumn(['payment_reference', 'payment_expires_at']);
        });
    }
};
