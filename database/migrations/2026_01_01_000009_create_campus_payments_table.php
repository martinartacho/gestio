<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('campus_enrollments')->onDelete('restrict');

            $table->decimal('amount', 10, 2);
            $table->enum('method', ['transfer', 'cash', 'card', 'domiciliacio'])->default('transfer');
            $table->date('payment_date');
            $table->enum('status', ['pending', 'completed', 'refunded'])->default('pending');
            $table->string('reference')->nullable();     // Núm. rebut / referència bancària

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['enrollment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_payments');
    }
};
