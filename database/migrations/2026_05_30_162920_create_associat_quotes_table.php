<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('associat_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('associat_members')->cascadeOnDelete();
            $table->foreignId('remittance_id')->nullable()->constrained('associat_sepa_remittances')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->enum('period', ['annual', 'semi-annual', 'quarterly', 'monthly'])->default('annual');
            $table->unsignedTinyInteger('period_number')->default(1);
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(['member_id', 'year', 'period', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('associat_quotes');
    }
};
