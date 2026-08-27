<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_news', function (Blueprint $table) {
            $table->enum('recipients', ['all', 'private', 'teachers', 'students', 'members'])
                  ->default('all')
                  ->after('version');
        });
    }

    public function down(): void
    {
        Schema::table('campus_news', function (Blueprint $table) {
            $table->dropColumn('recipients');
        });
    }
};
