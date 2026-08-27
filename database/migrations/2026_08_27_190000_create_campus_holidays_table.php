<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->date('date_end')->nullable();
            $table->string('label');
            $table->enum('type', ['festiu', 'no_lectiu'])->default('festiu');
            $table->boolean('recurring_yearly')->default(false);
            $table->timestamps();

            $table->index(['date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_holidays');
    }
};
