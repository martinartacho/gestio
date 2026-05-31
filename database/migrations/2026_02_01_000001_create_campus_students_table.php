<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_code', 6)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->tinyInteger('verification_attempts')->default(0);
            $table->string('password');
            $table->string('phone')->nullable();
            $table->text('dni')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->boolean('data_consent')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason', 300)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_students');
    }
};
