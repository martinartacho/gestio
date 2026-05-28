<?php

namespace App\Console\Commands;

use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CanviAllPass extends Command
{
    protected $signature = 'app:canvi-all-pass
                            {--force : Executa sense demanar confirmació}';

    protected $description = 'Actualitza les contrasenyes de tots els usuaris segons els grups definits al .env (SEEDER_*_PASSWORD)';

    public function handle(): int
    {
        $adminPass   = config('seeder.admin_password')
            ?? throw new \RuntimeException('SEEDER_ADMIN_PASSWORD no està definit al .env');
        $userPass    = config('seeder.user_password')
            ?? throw new \RuntimeException('SEEDER_USER_PASSWORD no està definit al .env');
        $studentPass = config('seeder.student_password')
            ?? throw new \RuntimeException('SEEDER_STUDENT_PASSWORD no està definit al .env');
        $teacherPass = config('seeder.teacher_password')
            ?? throw new \RuntimeException('SEEDER_TEACHER_PASSWORD no està definit al .env');

        $this->table(['Grup', 'Password'], [
            ['admin (rol admin)',           str_repeat('*', strlen($adminPass))],
            ['users (resta de rols)',       str_repeat('*', strlen($userPass))],
            ['campus_students',            str_repeat('*', strlen($studentPass))],
            ['campus_teachers',            str_repeat('*', strlen($teacherPass))],
        ]);

        if (! $this->option('force') && ! $this->confirm('Actualitzar totes les contrasenyes?')) {
            $this->line('Cancel·lat.');
            return self::SUCCESS;
        }

        // ── Admins ────────────────────────────────────────────────────────
        $admins = User::role('admin')->get();
        foreach ($admins as $user) {
            $user->update(['password' => Hash::make($adminPass)]);
        }

        // ── Resta d'usuaris (User sense rol admin) ────────────────────────
        $users = User::whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))->get();
        foreach ($users as $user) {
            $user->update(['password' => Hash::make($userPass)]);
        }

        // ── Alumnes ───────────────────────────────────────────────────────
        $students = CampusStudent::all();
        foreach ($students as $student) {
            $student->update(['password' => Hash::make($studentPass)]);
        }

        // ── Professors ────────────────────────────────────────────────────
        $teachers = CampusTeacher::all();
        foreach ($teachers as $teacher) {
            $teacher->update(['password' => Hash::make($teacherPass)]);
        }

        $this->table(['Grup', 'Actualitzats'], [
            ['admin',            $admins->count()],
            ['users',            $users->count()],
            ['campus_students',  $students->count()],
            ['campus_teachers',  $teachers->count()],
        ]);

        $this->info('Contrasenyes actualitzades correctament.');

        return self::SUCCESS;
    }
}
