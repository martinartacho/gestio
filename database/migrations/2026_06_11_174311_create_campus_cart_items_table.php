<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('campus_carts')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('campus_courses')->cascadeOnDelete();
            $table->decimal('price', 8, 2);
            $table->timestamps();

            $table->unique(['cart_id', 'course_id']);
            $table->index('cart_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_cart_items');
    }
};
