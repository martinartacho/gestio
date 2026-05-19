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
            $table->foreignId('student_id')->constrained('campus_students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('campus_courses')->onDelete('cascade');
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('amount', 8, 2)->nullable();
            $table->string('stripe_session_id')->nullable()->index();
            $table->string('stripe_payment_intent')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_enrollments');
    }
};
