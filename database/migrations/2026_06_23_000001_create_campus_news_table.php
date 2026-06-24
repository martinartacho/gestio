<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('summary')->nullable();
            $table->longText('body');
            $table->enum('category', ['campus', 'associats', 'sistema']);
            $table->string('version', 20)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_news');
    }
};
