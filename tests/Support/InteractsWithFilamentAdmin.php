<?php

namespace Tests\Support;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait InteractsWithFilamentAdmin
{
    protected static array $allPermissions = [
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        'seasons.view', 'seasons.create', 'seasons.edit', 'seasons.delete',
        'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
        'spaces.view', 'spaces.create', 'spaces.edit', 'spaces.delete',
        'timeslots.view', 'timeslots.create', 'timeslots.edit', 'timeslots.delete',
        'teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete',
        'courses.view', 'courses.create', 'courses.edit', 'courses.delete',
        'enrollments.view', 'enrollments.create', 'enrollments.edit', 'enrollments.delete',
        'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
        'teacher_payments.view', 'teacher_payments.create', 'teacher_payments.edit', 'teacher_payments.delete',
    ];

    protected function seedAllPermissions(): void
    {
        foreach (static::$allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }

    protected function createAdmin(): User
    {
        $this->seedAllPermissions();
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::all());
        $user = User::factory()->create(['active' => true]);
        $user->assignRole('admin');
        return $user;
    }

    protected function createTresoreria(): User
    {
        $this->seedAllPermissions();
        $role = Role::firstOrCreate(['name' => 'tresoreria', 'guard_name' => 'web']);
        $role->syncPermissions([
            'seasons.view', 'courses.view', 'teachers.view',
            'enrollments.view', 'enrollments.create', 'enrollments.edit', 'enrollments.delete',
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            'teacher_payments.view', 'teacher_payments.create', 'teacher_payments.edit', 'teacher_payments.delete',
        ]);
        $user = User::factory()->create(['active' => true]);
        $user->assignRole('tresoreria');
        return $user;
    }

    protected function createManager(): User
    {
        $this->seedAllPermissions();
        $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $role->syncPermissions([
            'seasons.view',
            'categories.view', 'categories.create', 'categories.edit',
            'spaces.view', 'timeslots.view',
            'teachers.view', 'teachers.create', 'teachers.edit',
            'courses.view', 'courses.create', 'courses.edit',
        ]);
        $user = User::factory()->create(['active' => true]);
        $user->assignRole('manager');
        return $user;
    }

    protected function createViewer(): User
    {
        $this->seedAllPermissions();
        $role = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $role->syncPermissions(['users.view', 'courses.view', 'teachers.view']);
        $user = User::factory()->create(['active' => true]);
        $user->assignRole('viewer');
        return $user;
    }
}
