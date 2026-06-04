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

        // Secretaria: professors, alumnes i socis + visualització de quotes
        $secretaria = Role::firstOrCreate(['name' => 'secretaria']);
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

        // Editor (mantenim per compatibilitat)
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions([
            'users.view', 'users.create', 'users.edit',
            'courses.view', 'courses.create', 'courses.edit',
            'teachers.view',
        ]);

        // Tresoreria: màxima responsabilitat financera (Campus + Associats + futur)
        $tresoreria = Role::firstOrCreate(['name' => 'tresoreria']);
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

        // Viewer: només lectura
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'users.view', 'courses.view', 'teachers.view',
        ]);

        // ── Usuaris de prova ──────────────────────────────────────────────
        $adminPassword = config('seeder.admin_password')
            ?? throw new \RuntimeException('SEEDER_ADMIN_PASSWORD no està definit al .env');
        $userPassword = config('seeder.user_password')
            ?? throw new \RuntimeException('SEEDER_USER_PASSWORD no està definit al .env');
        $mail = config('seeder.mail', 'app.test');

        User::firstOrCreate(['email' => "admin@{$mail}"], [
            'name' => 'Administrador', 'password' => bcrypt($adminPassword), 'active' => true,
        ])->syncRoles(['admin']);

        User::firstOrCreate(['email' => "manager@{$mail}"], [
            'name' => 'Manager Cursos', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['manager']);

        User::firstOrCreate(['email' => "secretaria@{$mail}"], [
            'name' => 'Secretaria', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['secretaria']);

        User::firstOrCreate(['email' => "editor@{$mail}"], [
            'name' => 'Editor Exemple', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['editor']);

        User::firstOrCreate(['email' => "tresoreria@{$mail}"], [
            'name' => 'Tresoreria', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['tresoreria']);

        User::firstOrCreate(['email' => "viewer@{$mail}"], [
            'name' => 'Viewer Exemple', 'password' => bcrypt($userPassword), 'active' => true,
        ])->syncRoles(['viewer']);

        $this->command->info('✅ Usuaris i permisos creats.');
        $this->command->table(
            ['Email', 'Rol', 'Contrasenya'],
            [
                ["admin@{$mail}",      'admin',      $adminPassword],
                ["manager@{$mail}",    'manager',    $userPassword],
                ["secretaria@{$mail}", 'secretaria', $userPassword],
                ["tresoreria@{$mail}", 'tresoreria', $userPassword],
                ["editor@{$mail}",     'editor',     $userPassword],
                ["viewer@{$mail}",     'viewer',     $userPassword],
            ]
        );

        // ── Configuració del lloc ─────────────────────────────────────────
        $this->call(SiteSettingsSeeder::class);

        // ── Dades del campus ──────────────────────────────────────────────
        $this->call(CampusSeeder::class);
        $this->call(CampusStudentSeeder::class);
        $this->call(CampusDocumentSeeder::class);
        $this->call(LmsLessonSeeder::class);

        // ── Dades del mòdul associats ─────────────────────────────────────
        $this->call(AssociatMemberSeeder::class);
        $this->call(AssociatQuoteSeeder::class);
    }
}
