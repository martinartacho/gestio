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
            $table->string('name');
            $table->smallInteger('year');
            $table->tinyInteger('quadrimester');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('start_date_enrollment')->nullable();
            $table->date('end_date_enrollment')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['year', 'quadrimester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_seasons');
    }
};
