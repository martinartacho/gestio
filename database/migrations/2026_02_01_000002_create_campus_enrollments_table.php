<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_enrollments', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('id')
                  ->constrained('campus_students')->onDelete('cascade');
            $table->decimal('amount', 8, 2)->nullable()->after('status');
            $table->string('stripe_session_id')->nullable()->index()->after('amount');
            $table->string('stripe_payment_intent')->nullable()->index()->after('stripe_session_id');
            $table->timestamp('paid_at')->nullable()->after('stripe_payment_intent');
        });

        // Extend status enum to include Stripe statuses (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE campus_enrollments MODIFY COLUMN status ENUM('pending','paid','confirmed','cancelled','completed','refunded') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE campus_enrollments MODIFY COLUMN status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('campus_enrollments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropIndex(['stripe_session_id']);
            $table->dropIndex(['stripe_payment_intent']);
            $table->dropColumn(['student_id', 'amount', 'stripe_session_id', 'stripe_payment_intent', 'paid_at']);
        });
    }
};
