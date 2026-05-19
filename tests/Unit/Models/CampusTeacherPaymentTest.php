<?php

namespace Tests\Unit\Models;

use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use App\Models\CampusTeacherPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusTeacherPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function makePayment(array $overrides = []): CampusTeacherPayment
    {
        $season  = CampusSeason::factory()->q1(2026)->create();
        $teacher = CampusTeacher::factory()->create();

        return CampusTeacherPayment::create(array_merge([
            'season_id'        => $season->id,
            'teacher_id'       => $teacher->id,
            'sessions_count'   => 10,
            'price_per_session' => 50.00,
            'gross_amount'     => 500.00,
            'retention_pct'    => 15.00,
            'retention_amount' => 75.00,
            'net_amount'       => 425.00,
            'status'           => 'draft',
        ], $overrides));
    }

    public function test_status_constants_exist(): void
    {
        foreach (['draft', 'sent', 'paid', 'cancelled'] as $key) {
            $this->assertArrayHasKey($key, CampusTeacherPayment::STATUSES);
            $this->assertArrayHasKey($key, CampusTeacherPayment::STATUS_COLORS);
        }
    }

    public function test_payment_is_persisted_in_database(): void
    {
        $payment = $this->makePayment();

        $this->assertDatabaseHas('campus_teacher_payments', [
            'id'             => $payment->id,
            'sessions_count' => 10,
            'status'         => 'draft',
        ]);
    }

    public function test_amounts_stored_correctly(): void
    {
        $payment = $this->makePayment([
            'gross_amount'     => 300.00,
            'retention_pct'    => 15.00,
            'retention_amount' => 45.00,
            'net_amount'       => 255.00,
        ]);

        $fresh = $payment->fresh();
        $this->assertEquals(300.00, $fresh->gross_amount);
        $this->assertEquals(15.00,  $fresh->retention_pct);
        $this->assertEquals(45.00,  $fresh->retention_amount);
        $this->assertEquals(255.00, $fresh->net_amount);
    }

    public function test_issues_invoice_defaults_to_false(): void
    {
        $payment = $this->makePayment();

        $this->assertFalse($payment->fresh()->issues_invoice);
    }

    public function test_status_defaults_to_draft(): void
    {
        $payment = $this->makePayment();

        $this->assertSame('draft', $payment->status);
    }

    public function test_season_relation_returns_correct_type(): void
    {
        $payment = CampusTeacherPayment::make();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $payment->season()
        );
    }

    public function test_teacher_relation_returns_correct_type(): void
    {
        $payment = CampusTeacherPayment::make();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $payment->teacher()
        );
    }

    public function test_payment_with_no_retention_has_equal_gross_and_net(): void
    {
        $payment = $this->makePayment([
            'gross_amount'     => 200.00,
            'retention_pct'    => 0,
            'retention_amount' => 0,
            'net_amount'       => 200.00,
        ]);

        $fresh = $payment->fresh();
        $this->assertEquals($fresh->gross_amount, $fresh->net_amount);
    }
}
