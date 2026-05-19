<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
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
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── Rols ─────────────────────────────────────────────────────────
        // Admin: tot
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Manager de cursos: gestiona cursos, professors i veu el catàleg
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'seasons.view',
            'categories.view', 'categories.create', 'categories.edit',
            'spaces.view',
            'timeslots.view',
            'teachers.view', 'teachers.create', 'teachers.edit',
            'courses.view', 'courses.create', 'courses.edit',
        ]);

        // Secretaria: visualització de cursos i professors
        $secretaria = Role::firstOrCreate(['name' => 'secretaria']);
        $secretaria->syncPermissions([
            'seasons.view',
            'categories.view',
            'spaces.view',
            'timeslots.view',
            'teachers.view',
            'courses.view',
        ]);

        // Editor (mantenim per compatibilitat)
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions([
            'users.view', 'users.create', 'users.edit',
            'courses.view', 'courses.create', 'courses.edit',
            'teachers.view',
        ]);

        // Viewer: només lectura
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'users.view', 'courses.view', 'teachers.view',
        ]);

        // ── Usuaris de prova ──────────────────────────────────────────────
        $adminPassword = env('SEEDER_ADMIN_PASSWORD')
            ?? throw new \RuntimeException('SEEDER_ADMIN_PASSWORD no està definit al .env');
        $userPassword = env('SEEDER_USER_PASSWORD')
            ?? throw new \RuntimeException('SEEDER_USER_PASSWORD no està definit al .env');

        User::firstOrCreate(['email' => 'admin@app.com'], [
            'name' => 'Administrador', 'password' => bcrypt($adminPassword), 'active' => true,
        ])->syncRoles(['admin']);

        User::firstOrCreate(['email' => 'manager@app.com'], [
            'name' => 'Manager Cursos', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['manager']);

        User::firstOrCreate(['email' => 'secretaria@app.com'], [
            'name' => 'Secretaria', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['secretaria']);

        User::firstOrCreate(['email' => 'editor@app.com'], [
            'name' => 'Editor Exemple', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['editor']);

        User::firstOrCreate(['email' => 'viewer@app.com'], [
            'name' => 'Viewer Exemple', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['viewer']);

        $this->command->info('✅ Usuaris i permisos creats.');
        $this->command->table(
            ['Email', 'Rol', 'Contrasenya'],
            [
                ['admin@app.com',      'admin',      $adminPassword],
                ['manager@app.com',    'manager',    $userPassword],
                ['secretaria@app.com', 'secretaria', $userPassword],
                ['editor@app.com',     'editor',     $userPassword],
                ['viewer@app.com',     'viewer',     $userPassword],
            ]
        );

        // ── Dades del campus ──────────────────────────────────────────────
        $this->call(CampusSeeder::class);
        $this->call(CampusStudentSeeder::class);
    }
}
