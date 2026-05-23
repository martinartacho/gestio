<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campus_enrollments', function (Blueprint $table) {
            $table->decimal('refunded_amount', 8, 2)->nullable()->after('amount');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
            $table->string('refund_notes', 500)->nullable()->after('refunded_at');
            $table->string('stripe_refund_id', 100)->nullable()->after('stripe_payment_intent');
        });
    }

    public function down(): void
    {
        Schema::table('campus_enrollments', function (Blueprint $table) {
            $table->dropColumn(['refunded_amount', 'refunded_at', 'refund_notes', 'stripe_refund_id']);
        });
    }
};
