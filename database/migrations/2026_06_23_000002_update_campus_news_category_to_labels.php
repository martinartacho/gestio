<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_news', function (Blueprint $table) {
            $table->json('labels')->nullable()->after('body');
        });

        // Migrar dades existents: category → labels (array)
        DB::table('campus_news')->get()->each(function ($row) {
            DB::table('campus_news')->where('id', $row->id)->update([
                'labels' => json_encode([$row->category]),
            ]);
        });

        Schema::table('campus_news', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('campus_news', function (Blueprint $table) {
            $table->enum('category', ['campus', 'associats', 'sistema'])->after('body');
        });

        DB::table('campus_news')->get()->each(function ($row) {
            $labels = json_decode($row->labels, true) ?? [];
            DB::table('campus_news')->where('id', $row->id)->update([
                'category' => $labels[0] ?? 'sistema',
            ]);
        });

        Schema::table('campus_news', function (Blueprint $table) {
            $table->dropColumn('labels');
        });
    }
};
