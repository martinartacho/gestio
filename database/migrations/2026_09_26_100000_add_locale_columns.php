<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $userTables = ['users', 'campus_students', 'campus_teachers', 'associat_members'];

    public function up(): void
    {
        foreach ($this->userTables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('locale', 5)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->userTables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('locale');
            });
        }
    }
};
