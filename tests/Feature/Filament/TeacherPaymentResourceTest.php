<?php

namespace Tests\Feature\Filament;

use App\Models\CampusSeason;
use App\Models\CampusTeacher;
use App\Models\CampusTeacherPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class TeacherPaymentResourceTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdmin();
    }

    private function makeTeacherPayment(): CampusTeacherPayment
    {
        $season  = CampusSeason::factory()->q1(2026)->create();
        $teacher = CampusTeacher::factory()->create();

        return CampusTeacherPayment::create([
            'season_id'         => $season->id,
            'teacher_id'        => $teacher->id,
            'sessions_count'    => 6,
            'price_per_session' => 50.00,
            'gross_amount'      => 300.00,
            'retention_pct'     => 15.00,
            'retention_amount'  => 45.00,
            'net_amount'        => 255.00,
            'status'            => 'draft',
        ]);
    }

    public function test_admin_can_list_teacher_payments(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/teacher-payments')
             ->assertSuccessful();
    }

    public function test_admin_can_access_create_teacher_payment_form(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/teacher-payments/create')
             ->assertSuccessful();
    }

    public function test_admin_can_access_edit_teacher_payment_form(): void
    {
        $payment = $this->makeTeacherPayment();

        $this->actingAs($this->admin)
             ->get("/admin/teacher-payments/{$payment->id}/edit")
             ->assertSuccessful();
    }

    public function test_tresoreria_can_list_teacher_payments(): void
    {
        $user = $this->createTresoreria();

        $this->actingAs($user)
             ->get('/admin/teacher-payments')
             ->assertSuccessful();
    }

    public function test_tresoreria_can_access_create_form(): void
    {
        $user = $this->createTresoreria();

        $this->actingAs($user)
             ->get('/admin/teacher-payments/create')
             ->assertSuccessful();
    }

    public function test_manager_cannot_access_teacher_payments(): void
    {
        $user = $this->createManager();

        $this->actingAs($user)
             ->get('/admin/teacher-payments')
             ->assertStatus(403);
    }

    public function test_payment_record_stored_in_database(): void
    {
        $payment = $this->makeTeacherPayment();

        $this->assertDatabaseHas('campus_teacher_payments', [
            'id'             => $payment->id,
            'gross_amount'   => 300.00,
            'net_amount'     => 255.00,
            'status'         => 'draft',
        ]);
    }

    public function test_payment_status_can_be_updated_to_paid(): void
    {
        $payment = $this->makeTeacherPayment();
        $payment->update(['status' => 'paid', 'paid_at' => now()]);

        $this->assertDatabaseHas('campus_teacher_payments', [
            'id'     => $payment->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull($payment->fresh()->paid_at);
    }
}
