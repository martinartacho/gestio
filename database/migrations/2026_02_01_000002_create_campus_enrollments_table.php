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
            $table->foreignId('student_id')->nullable()->constrained('campus_students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('campus_courses')->restrictOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->string('dni', 20)->nullable();
            $table->enum('status', ['pending', 'paid', 'confirmed', 'cancelled', 'completed', 'refunded'])->default('pending');
            $table->string('payment_method', 20)->nullable();
            $table->string('payment_reference', 12)->nullable()->unique();
            $table->timestamp('payment_expires_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->decimal('refunded_amount', 8, 2)->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_payment_intent')->nullable();
            $table->string('stripe_refund_id', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_notes', 500)->nullable();
            $table->date('enrollment_date');
            $table->string('bank_iban', 34)->nullable();
            $table->string('bank_holder')->nullable();
            $table->boolean('rgpd_accepted')->default(false);
            $table->timestamp('rgpd_accepted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'status']);
            $table->index('email');
            $table->index('payment_method');
            $table->index('stripe_session_id');
            $table->index('stripe_payment_intent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_enrollments');
    }
};
