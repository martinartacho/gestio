<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Segona ronda — se'n van escapar tres a la revisió d'ahir (columnes
     * "slug" que vaig filtrar per error, i campus_queue_entries perquè la
     * migració que hi va afegir tenant_id no va tocar l'índex existent).
     * Trobat en real: crear una categoria amb el mateix nom a un segon
     * tenant petava (Duplicate entry 'arts-i-humanitats').
     */
    public function up(): void
    {
        Schema::table('campus_categories', function (Blueprint $table) {
            $table->dropUnique('campus_categories_slug_unique');
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::table('campus_courses', function (Blueprint $table) {
            $table->dropUnique('campus_courses_slug_unique');
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::table('campus_queue_entries', function (Blueprint $table) {
            $table->dropUnique('campus_queue_entries_email_unique');
            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campus_categories', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'slug']);
            $table->unique('slug');
        });

        Schema::table('campus_courses', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'slug']);
            $table->unique('slug');
        });

        Schema::table('campus_queue_entries', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique('email');
        });
    }
};
