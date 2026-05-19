<?php

namespace Tests\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait InteractsWithFilamentAdmin
{
    protected function seedAllPermissions(): void
    {
        $permissions = [
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'seasons.view',    'seasons.create',    'seasons.edit',    'seasons.delete',
            'courses.view',    'courses.create',    'courses.edit',    'courses.delete',
            'teachers.view',   'teachers.create',   'teachers.edit',   'teachers.delete',
            'spaces.view',     'spaces.create',     'spaces.edit',     'spaces.delete',
            'timeslots.view',  'timeslots.create',  'timeslots.edit',  'timeslots.delete',
            'users.view',      'users.create',      'users.edit',      'users.delete',
            'roles.view',      'roles.create',      'roles.edit',      'roles.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }

    protected function createAdmin(): User
    {
        $this->seedAllPermissions();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::where('guard_name', 'web')->get());

        $user = User::factory()->create(['active' => true]);
        $user->assignRole('admin');

        return $user;
    }

    protected function createViewer(): User
    {
        $this->seedAllPermissions();

        Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        $user = User::factory()->create(['active' => true]);
        $user->assignRole('viewer');

        return $user;
    }
}
