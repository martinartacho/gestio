<?php

namespace App\Console\Commands;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusStudent;
use Illuminate\Console\Command;

class ClearEnrollments extends Command
{
    protected $signature = 'enrollments:clear
        {--course=     : Slug o ID del curs}
        {--student=    : Email o ID de l\'alumne}
        {--status=     : Filtra per estat (pending,paid,confirmed,cancelled...)}
        {--force       : No demana confirmació}
        {--dry-run     : Mostra què s\'esborraria sense esborrar}';

    protected $description = 'Esborra matrícules per a proves manuals (curs, alumne o combinació).';

    public function handle(): int
    {
        $courseOpt  = $this->option('course');
        $studentOpt = $this->option('student');
        $statusOpt  = $this->option('status');

        if (! $courseOpt && ! $studentOpt) {
            $this->error('Cal especificar almenys --course o --student.');
            $this->line('  Exemples:');
            $this->line('    php artisan enrollments:clear --course=mat-01');
            $this->line('    php artisan enrollments:clear --student=alumne@exemple.cat');
            $this->line('    php artisan enrollments:clear --course=mat-01 --student=alumne@exemple.cat');
            $this->line('    php artisan enrollments:clear --course=mat-01 --status=pending --dry-run');
            return self::FAILURE;
        }

        // ── Resolució del curs ────────────────────────────────────────────────
        $course = null;
        if ($courseOpt) {
            $course = is_numeric($courseOpt)
                ? CampusCourse::find((int) $courseOpt)
                : CampusCourse::where('slug', $courseOpt)->first();

            if (! $course) {
                $this->error("Curs no trobat: «{$courseOpt}»");
                return self::FAILURE;
            }
        }

        // ── Resolució de l'alumne ─────────────────────────────────────────────
        $student = null;
        if ($studentOpt) {
            $student = is_numeric($studentOpt)
                ? CampusStudent::find((int) $studentOpt)
                : CampusStudent::where('email', $studentOpt)->first();

            if (! $student) {
                $this->error("Alumne no trobat: «{$studentOpt}»");
                return self::FAILURE;
            }
        }

        // ── Construcció de la query ───────────────────────────────────────────
        $query = CampusEnrollment::query();

        if ($course) {
            $query->where('course_id', $course->id);
        }
        if ($student) {
            $query->where('student_id', $student->id);
        }
        if ($statusOpt) {
            $statuses = array_map('trim', explode(',', $statusOpt));
            $query->whereIn('status', $statuses);
        }

        $enrollments = $query->with('course:id,title,code')->get();

        if ($enrollments->isEmpty()) {
            $this->info('Cap matrícula trobada amb els filtres indicats.');
            return self::SUCCESS;
        }

        // ── Resum ─────────────────────────────────────────────────────────────
        $this->newLine();
        $this->line("<fg=yellow>Matrícules que s'esborraran ({$enrollments->count()}):</>");
        $this->table(
            ['ID', 'Alumne', 'Email', 'Curs', 'Estat', 'Mètode', 'Referència'],
            $enrollments->map(fn($e) => [
                $e->id,
                $e->full_name,
                $e->email ?? '—',
                ($e->course?->code ?? '?') . ' ' . str($e->course?->title ?? '?')->limit(30),
                $e->status,
                $e->payment_method ?? '—',
                $e->payment_reference ?? '—',
            ])
        );

        if ($this->option('dry-run')) {
            $this->warn('--dry-run actiu: cap canvi realitzat.');
            return self::SUCCESS;
        }

        // ── Confirmació ───────────────────────────────────────────────────────
        if (! $this->option('force')) {
            if (! $this->confirm("Estàs segur que vols esborrar {$enrollments->count()} matrícula(es)?", false)) {
                $this->line('Operació cancel·lada.');
                return self::SUCCESS;
            }
        }

        // ── Esborrat ──────────────────────────────────────────────────────────
        $deleted = $query->delete();
        $this->info("✓ {$deleted} matrícula(es) esborrades.");

        return self::SUCCESS;
    }
}
