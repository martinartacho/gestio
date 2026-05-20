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
        Schema::table('campus_seasons', function (Blueprint $table) {
            $table->date('start_date_enrollment')->nullable()->after('end_date');
            $table->date('end_date_enrollment')->nullable()->after('start_date_enrollment');
        });
    }

    public function down(): void
    {
        Schema::table('campus_seasons', function (Blueprint $table) {
            $table->dropColumn(['start_date_enrollment', 'end_date_enrollment']);
        });
    }
};
