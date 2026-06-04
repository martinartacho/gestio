<?php

namespace Tests\Feature\Filament;

use App\Models\AssociatSepaRemittance;
use App\Models\User;
use App\Settings\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class TresoreriaRemittanceResourceTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdmin();
        app(SettingStore::class)->set('associats_enabled', true);
    }

    private function makeRemittance(): AssociatSepaRemittance
    {
        return AssociatSepaRemittance::create([
            'reference'      => 'REMESA-2026-001',
            'year'           => 2026,
            'execution_date' => '2026-02-01',
            'total_amount'   => 480.00,
            'total_transactions' => 6,
            'status'         => 'draft',
        ]);
    }

    public function test_admin_can_list_tresoreria_remittances(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/tresoreria-remittances')
             ->assertSuccessful();
    }

    public function test_admin_can_access_create_tresoreria_remittance_form(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/tresoreria-remittances/create')
             ->assertSuccessful();
    }

    public function test_admin_can_access_edit_tresoreria_remittance_form(): void
    {
        $remittance = $this->makeRemittance();

        $this->actingAs($this->admin)
             ->get("/admin/tresoreria-remittances/{$remittance->id}/edit")
             ->assertSuccessful();
    }

    public function test_tresoreria_can_list_remittances(): void
    {
        $user = $this->createTresoreria();

        $this->actingAs($user)
             ->get('/admin/tresoreria-remittances')
             ->assertSuccessful();
    }

    public function test_tresoreria_can_access_create_form(): void
    {
        $user = $this->createTresoreria();

        $this->actingAs($user)
             ->get('/admin/tresoreria-remittances/create')
             ->assertSuccessful();
    }

    public function test_manager_cannot_access_tresoreria_remittances(): void
    {
        $user = $this->createManager();

        $this->actingAs($user)
             ->get('/admin/tresoreria-remittances')
             ->assertStatus(403);
    }

    public function test_remittance_record_stored_in_database(): void
    {
        $remittance = $this->makeRemittance();

        $this->assertDatabaseHas('associat_sepa_remittances', [
            'id'           => $remittance->id,
            'reference'    => 'REMESA-2026-001',
            'year'         => 2026,
            'total_amount' => 480.00,
            'status'       => 'draft',
        ]);
    }

    public function test_remittance_status_can_be_updated_to_processed(): void
    {
        $remittance = $this->makeRemittance();
        $remittance->update(['status' => 'processed']);

        $this->assertDatabaseHas('associat_sepa_remittances', [
            'id'     => $remittance->id,
            'status' => 'processed',
        ]);
    }
}
