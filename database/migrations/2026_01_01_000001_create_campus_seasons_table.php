<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Tardor 2026", "Primavera 2027"
            $table->smallInteger('year');                    // 2026
            $table->tinyInteger('quadrimester');             // 1 = tardor/setembre-gener, 2 = primavera/febrer-juny
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['year', 'quadrimester']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_seasons');
    }
};
