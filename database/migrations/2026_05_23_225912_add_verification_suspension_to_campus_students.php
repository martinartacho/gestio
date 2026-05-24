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
        Schema::table('campus_students', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->timestamp('suspended_at')->nullable()->after('updated_at');
            $table->string('suspension_reason', 300)->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('campus_students', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'suspended_at', 'suspension_reason']);
        });
    }
};
