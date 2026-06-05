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
        Schema::table('campus_teachers', function (Blueprint $table) {
            $table->string('verification_code', 10)->nullable()->after('remember_token');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->unsignedTinyInteger('verification_attempts')->default(0)->after('verification_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('campus_teachers', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'verification_code_expires_at', 'verification_attempts']);
        });
    }
};
