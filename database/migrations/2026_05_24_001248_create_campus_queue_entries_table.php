<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campus_queue_entries', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->index();
            $table->unsignedInteger('queue_number');
            $table->string('access_code', 6)->nullable();
            $table->timestamp('slot_starts_at');
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('accessed_at')->nullable();
            $table->timestamp('access_expires_at')->nullable();
            $table->string('status', 20)->default('waiting'); // waiting|notified|accessed|expired
            $table->timestamps();

            $table->unique('email');
            $table->index(['status', 'slot_starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campus_queue_entries');
    }
};
