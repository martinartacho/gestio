<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Sala d'actes", "Aula mitjana 1"
            $table->string('code')->unique();                // SA, AM1, AP1, EXT, ONLINE
            $table->integer('capacity')->default(0);
            $table->enum('type', [
                'sala_actes',
                'mitjana',
                'petita',
                'polivalent',
                'extern',
                'virtual',                                   // per cursos online
            ])->default('polivalent');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_spaces');
    }
};
