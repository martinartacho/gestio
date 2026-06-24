<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder idempotent de rols i permisos.
 * Segur per executar en producció després de cada desplegament.
 * Usar: php artisan db:seed --class=RolesPermissionsSeeder
 */
class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Permisos ──────────────────────────────────────────────────────
        $permissions = [
            // Usuaris
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Rols
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            // Temporades
            'seasons.view', 'seasons.create', 'seasons.edit', 'seasons.delete',
            // Categories
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            // Espais
            'spaces.view', 'spaces.create', 'spaces.edit', 'spaces.delete',
            // Franges horàries
            'timeslots.view', 'timeslots.create', 'timeslots.edit', 'timeslots.delete',
            // Professors
            'teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete',
            // Cursos
            'courses.view', 'courses.create', 'courses.edit', 'courses.delete',
            // Inscripcions
            'enrollments.view', 'enrollments.create', 'enrollments.edit', 'enrollments.delete',
            // Pagaments
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            // Liquidacions professors
            'teacher_payments.view', 'teacher_payments.create', 'teacher_payments.edit', 'teacher_payments.delete',
            // Socis (Associats)
            'members.view', 'members.create', 'members.edit', 'members.delete',
            // Quotes de socis
            'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.delete',
            // Remeses SEPA de socis
            'sepa.view', 'sepa.create', 'sepa.edit', 'sepa.delete',
            // Notícies
            'news.view', 'news.create', 'news.edit', 'news.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $this->command->info('✅ ' . count($permissions) . ' permisos creats/verificats.');

        // ── Rols ─────────────────────────────────────────────────────────

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'seasons.view',
            'categories.view', 'categories.create', 'categories.edit',
            'spaces.view',
            'timeslots.view',
            'teachers.view', 'teachers.create', 'teachers.edit',
            'courses.view', 'courses.create', 'courses.edit',
            'news.view', 'news.create', 'news.edit',
        ]);

        $secretaria = Role::firstOrCreate(['name' => 'secretaria', 'guard_name' => 'web']);
        $secretaria->syncPermissions([
            'seasons.view',
            'categories.view',
            'spaces.view',
            'timeslots.view',
            'teachers.view',
            'courses.view',
            'enrollments.view',
            'members.view', 'members.create', 'members.edit', 'members.delete',
            'quotes.view',
        ]);

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'users.view', 'users.create', 'users.edit',
            'courses.view', 'courses.create', 'courses.edit',
            'teachers.view',
        ]);

        $tresoreria = Role::firstOrCreate(['name' => 'tresoreria', 'guard_name' => 'web']);
        $tresoreria->syncPermissions([
            'seasons.view',
            'courses.view',
            'teachers.view', 'teachers.edit',
            'enrollments.view', 'enrollments.create', 'enrollments.edit', 'enrollments.delete',
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            'teacher_payments.view', 'teacher_payments.create', 'teacher_payments.edit', 'teacher_payments.delete',
            'quotes.view', 'quotes.create', 'quotes.edit', 'quotes.delete',
            'sepa.view', 'sepa.create', 'sepa.edit', 'sepa.delete',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'users.view', 'courses.view', 'teachers.view',
        ]);

        $this->command->info('✅ 6 rols sincronitzats.');
    }
}
