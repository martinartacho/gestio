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
            // stripe | transfer | bizum | cash | paypal | free | null (registres antics)
            $table->string('payment_method', 20)->nullable()->after('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('campus_enrollments', function (Blueprint $table) {
            $table->dropIndex(['payment_method']);
            $table->dropColumn('payment_method');
        });
    }
};
