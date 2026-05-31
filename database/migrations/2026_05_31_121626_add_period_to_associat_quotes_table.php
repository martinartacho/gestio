<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('associat_quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('associat_quotes', 'period')) {
                $table->enum('period', ['annual', 'semi-annual', 'quarterly', 'monthly'])
                    ->default('annual')
                    ->after('year');
            }
            if (!Schema::hasColumn('associat_quotes', 'period_number')) {
                $table->unsignedTinyInteger('period_number')
                    ->default(1)
                    ->after('period');
            }
        });

        // Afegir primer el nou índex (MySQL necessita índex per la FK) i després eliminar l'antic
        $indexes = $this->getIndexNames();
        if (!in_array('associat_quotes_member_id_year_period_period_number_unique', $indexes)) {
            Schema::table('associat_quotes', fn (Blueprint $t) =>
                $t->unique(['member_id', 'year', 'period', 'period_number'])
            );
        }
        if (in_array('associat_quotes_member_id_year_unique', $indexes)) {
            Schema::table('associat_quotes', fn (Blueprint $t) =>
                $t->dropUnique(['member_id', 'year'])
            );
        }
    }

    public function down(): void
    {
        $indexes = $this->getIndexNames();
        if (!in_array('associat_quotes_member_id_year_unique', $indexes)) {
            Schema::table('associat_quotes', fn (Blueprint $t) =>
                $t->unique(['member_id', 'year'])
            );
        }
        if (in_array('associat_quotes_member_id_year_period_period_number_unique', $indexes)) {
            Schema::table('associat_quotes', fn (Blueprint $t) =>
                $t->dropUnique(['member_id', 'year', 'period', 'period_number'])
            );
        }
        Schema::table('associat_quotes', function (Blueprint $table) {
            $table->dropColumn(['period', 'period_number']);
        });
    }

    private function getIndexNames(): array
    {
        return collect(DB::select("SHOW INDEX FROM associat_quotes"))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->all();
    }
};
