<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('campus_courses')->onDelete('restrict');

            // Dades de l'alumne
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('dni', 20)->nullable();       // NIF / NIE / Passaport

            // Estat
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])
                  ->default('pending');
            $table->date('enrollment_date');

            // Dades bancàries (domiciliació)
            $table->string('bank_iban', 34)->nullable();
            $table->string('bank_holder')->nullable();   // Titular del compte

            // RGPD
            $table->boolean('rgpd_accepted')->default(false);
            $table->timestamp('rgpd_accepted_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_enrollments');
    }
};
