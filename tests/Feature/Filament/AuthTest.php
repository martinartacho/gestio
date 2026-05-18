<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::factory()->create(['active' => true]);
        $user->assignRole('admin');

        return $user;
    }

    private function createViewer(): User
    {
        Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        $user = User::factory()->create(['active' => true]);
        $user->assignRole('viewer');

        return $user;
    }

    public function test_active_admin_can_access_panel(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
             ->get('/admin')
             ->assertSuccessful();
    }

    public function test_inactive_admin_cannot_access_panel(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::factory()->create(['active' => false]);
        $user->assignRole('admin');

        $this->actingAs($user)
             ->get('/admin')
             ->assertStatus(403);
    }

    public function test_user_without_admin_role_cannot_access_panel(): void
    {
        $viewer = $this->createViewer();

        $this->actingAs($viewer)
             ->get('/admin')
             ->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')
             ->assertRedirect();
    }
}
