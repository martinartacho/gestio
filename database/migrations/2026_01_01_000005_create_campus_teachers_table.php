<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->string('code', 20)->nullable()->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();

            // Dades personals ampliades
            $table->string('dni', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->text('observacions')->nullable();

            // Dades professionals
            $table->string('degree', 20)->nullable();
            $table->string('title')->nullable();
            $table->json('areas')->nullable();
            $table->date('hiring_date')->nullable();

            // Dades fiscals
            $table->string('fiscal_situation', 30)->nullable();
            $table->boolean('needs_payment')->default(true);
            $table->boolean('invoice')->nullable();
            $table->string('payment_type', 20)->nullable();

            // Dades bancàries (encriptades al model)
            $table->text('bank_iban')->nullable();
            $table->text('bank_holder')->nullable();
            $table->text('fiscal_id')->nullable();

            // Beneficiari (encriptat al model)
            $table->string('beneficiary_dni', 20)->nullable();
            $table->text('beneficiary_iban')->nullable();
            $table->text('beneficiary_holder')->nullable();
            $table->string('beneficiary_fiscal_situation', 30)->nullable();
            $table->boolean('beneficiary_invoice')->nullable();
            $table->string('beneficiary_city')->nullable();
            $table->string('beneficiary_postal_code', 10)->nullable();

            // Consentiments RGPD
            $table->boolean('data_consent')->default(false);
            $table->boolean('fiscal_responsibility')->default(false);
            $table->boolean('ceded_confirmation')->default(false);

            // Seguiment del pagament
            $table->enum('payment_status', ['pending', 'confirmed', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->string('payment_pdf_path')->nullable();

            $table->json('metadata')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_teachers');
    }
};
