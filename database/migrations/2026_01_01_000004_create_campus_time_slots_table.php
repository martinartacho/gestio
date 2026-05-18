<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_time_slots', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week');              // 1=dl, 2=dt, 3=dc, 4=dj, 5=dv
            $table->string('code');                          // DL10, DC16, DJ18
            $table->time('start_time');                      // 10:00:00
            $table->time('end_time');                        // 11:30:00
            $table->string('description');                   // "Dilluns 10-11:30"
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['day_of_week', 'code']);
            $table->index(['day_of_week', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_time_slots');
    }
};
