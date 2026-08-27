<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Rols i permisos ───────────────────────────────────────────────
        $this->call(RolesPermissionsSeeder::class);

        // ── Usuaris de prova ──────────────────────────────────────────────
        $adminPassword = config('seeder.admin_password')
            ?? throw new \RuntimeException('SEEDER_ADMIN_PASSWORD no està definit al .env');
        $userPassword = config('seeder.user_password')
            ?? throw new \RuntimeException('SEEDER_USER_PASSWORD no està definit al .env');
        $mail = config('seeder.mail', 'app.test');

        // super-admin: només si s'ha definit un email real a .env (mai hardcodejat al codi)
        if ($superAdminEmail = config('seeder.super_admin_email')) {
            User::firstOrCreate(['email' => $superAdminEmail], [
                'name' => 'Super Admin', 'password' => bcrypt($adminPassword), 'active' => true,
            ])->syncRoles(['super-admin']);
        }

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

        $this->command->info('✅ Usuaris de prova creats.');
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
        $this->call(DemoSeeder::class);
        $this->call(CampusDocumentSeeder::class);
        $this->call(LmsLessonSeeder::class);

        // ── Dades del mòdul associats ─────────────────────────────────────
        $this->call(AssociatMemberSeeder::class);
        $this->call(AssociatQuoteSeeder::class);

        // ── Notícies per defecte ──────────────────────────────────────────
        $this->call(CampusNewsSeeder::class);
    }
}
