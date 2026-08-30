<?php

namespace Tests\Feature\Filament;

use App\Models\AssociatMember;
use App\Models\AssociatQuote;
use App\Models\User;
use App\Settings\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class TresoreriaQuoteResourceTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdmin();
        app(SettingStore::class)->set('associats_enabled', true);
    }

    private function makeMember(): AssociatMember
    {
        return AssociatMember::create([
            'tenant_id'     => \App\Models\Tenant::where('slug', 'campus')->value('id'),
            'member_number' => 1001,
            'first_name'    => 'Joan',
            'last_name'     => 'Puig',
            'email'         => 'joan@test.com',
            'password'      => bcrypt('secret'),
            'status'        => 'active',
        ]);
    }

    private function makeQuote(): AssociatQuote
    {
        $member = $this->makeMember();
        return AssociatQuote::create([
            'tenant_id'     => \App\Models\Tenant::where('slug', 'campus')->value('id'),
            'member_id'     => $member->id,
            'year'          => 2026,
            'period'        => 'annual',
            'period_number' => 1,
            'amount'        => 80.00,
            'status'        => 'pending',
        ]);
    }

    public function test_admin_can_list_tresoreria_quotes(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/campus/tresoreria-quotes')
             ->assertSuccessful();
    }

    public function test_admin_can_access_create_tresoreria_quote_form(): void
    {
        $this->actingAs($this->admin)
             ->get('/admin/campus/tresoreria-quotes/create')
             ->assertSuccessful();
    }

    public function test_admin_can_access_edit_tresoreria_quote_form(): void
    {
        $quote = $this->makeQuote();

        $this->actingAs($this->admin)
             ->get("/admin/campus/tresoreria-quotes/{$quote->id}/edit")
             ->assertSuccessful();
    }

    public function test_tresoreria_can_list_tresoreria_quotes(): void
    {
        $user = $this->createTresoreria();

        $this->actingAs($user)
             ->get('/admin/campus/tresoreria-quotes')
             ->assertSuccessful();
    }

    public function test_tresoreria_can_access_create_form(): void
    {
        $user = $this->createTresoreria();

        $this->actingAs($user)
             ->get('/admin/campus/tresoreria-quotes/create')
             ->assertSuccessful();
    }

    public function test_manager_cannot_access_tresoreria_quotes(): void
    {
        $user = $this->createManager();

        $this->actingAs($user)
             ->get('/admin/campus/tresoreria-quotes')
             ->assertStatus(403);
    }

    public function test_quote_record_stored_in_database(): void
    {
        $quote = $this->makeQuote();

        $this->assertDatabaseHas('associat_quotes', [
            'id'     => $quote->id,
            'year'   => 2026,
            'amount' => 80.00,
            'status' => 'pending',
        ]);
    }

    public function test_quote_status_can_be_updated_to_paid(): void
    {
        $quote = $this->makeQuote();
        $quote->update(['status' => 'paid', 'paid_at' => now()]);

        $this->assertDatabaseHas('associat_quotes', [
            'id'     => $quote->id,
            'status' => 'paid',
        ]);
        $this->assertNotNull($quote->fresh()->paid_at);
    }
}
