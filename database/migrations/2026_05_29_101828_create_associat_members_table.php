<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('associat_members', function (Blueprint $table) {
            $table->id();
            $table->string('member_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->text('dni')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->enum('status', ['active', 'pending', 'cancelled'])->default('pending');
            $table->date('joined_at')->nullable();
            $table->date('cancelled_at')->nullable();
            $table->boolean('data_consent')->default(false);
            $table->text('bank_iban')->nullable();
            $table->string('bank_holder')->nullable();
            $table->string('mandate_reference')->nullable()->unique();
            $table->date('mandate_signed_at')->nullable();
            $table->enum('mandate_sequence', ['FRST', 'RCUR'])->nullable();
            $table->enum('mandate_method', ['paper', 'electronic', 'web'])->nullable();
            $table->string('mandate_document')->nullable();
            $table->string('mandate_ip', 45)->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->string('reset_token')->nullable()->unique();
            $table->timestamp('reset_token_expires_at')->nullable();
            $table->foreignId('campus_student_id')->nullable()->constrained('campus_students')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('associat_members');
    }
};
