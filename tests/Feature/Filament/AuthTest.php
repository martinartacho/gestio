<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithFilamentAdmin;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, InteractsWithFilamentAdmin;

    public function test_active_admin_can_access_panel(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
             ->get('/admin')
             ->assertSuccessful();
    }

    public function test_inactive_admin_cannot_access_panel(): void
    {
        $admin = $this->createAdmin();
        $admin->update(['active' => false]);

        $this->actingAs($admin)
             ->get('/admin')
             ->assertStatus(403);
    }

    public function test_user_without_panel_role_cannot_access_panel(): void
    {
        $viewer = $this->createViewer();

        $this->actingAs($viewer)
             ->get('/admin')
             ->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function test_active_tresoreria_can_access_panel(): void
    {
        $user = $this->createTresoreria();

        $this->actingAs($user)
             ->get('/admin')
             ->assertSuccessful();
    }

    public function test_active_manager_can_access_panel(): void
    {
        $user = $this->createManager();

        $this->actingAs($user)
             ->get('/admin')
             ->assertSuccessful();
    }
}
