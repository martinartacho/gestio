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
        // ── Permisos ──────────────────────────────────────────────────────────
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── Roles ─────────────────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions(['users.view', 'users.create', 'users.edit']);

        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions(['users.view']);

        // ── Usuarios de prueba ────────────────────────────────────────────────
        $adminPassword = env('SEEDER_ADMIN_PASSWORD', 'password');
        $userPassword  = env('SEEDER_USER_PASSWORD', 'password');

        User::firstOrCreate(['email' => 'admin@app.com'], [
            'name' => 'Administrador', 'password' => bcrypt($adminPassword), 'active' => true,
        ])->assignRole('admin');

        User::firstOrCreate(['email' => 'editor@app.com'], [
            'name' => 'Editor Ejemplo', 'password' => bcrypt($userPassword), 'active' => true,
        ])->assignRole('editor');

        User::firstOrCreate(['email' => 'viewer@app.com'], [
            'name' => 'Viewer Ejemplo', 'password' => bcrypt($userPassword), 'active' => true,
        ])->assignRole('viewer');

        $this->command->info('✅ Seeder completado.');
        $this->command->table(
            ['Email', 'Rol', 'Contraseña', 'Acceso panel'],
            [
                ['admin@app.com',  'admin',  $adminPassword, '✅ Sí'],
                ['editor@app.com', 'editor', $userPassword,  '❌ No (sin rol admin)'],
                ['viewer@app.com', 'viewer', $userPassword,  '❌ No (sin rol admin)'],
            ]
        );
    }
}