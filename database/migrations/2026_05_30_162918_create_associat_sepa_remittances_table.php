<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('associat_sepa_remittances', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->unsignedSmallInteger('year');
            $table->date('execution_date');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->unsignedInteger('total_transactions')->default(0);
            $table->enum('status', ['draft', 'generated', 'submitted', 'processed'])->default('draft');
            $table->string('xml_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('associat_sepa_remittances');
    }
};
