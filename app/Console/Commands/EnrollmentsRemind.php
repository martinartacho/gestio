<?php

namespace App\Console\Commands;

use App\Mail\Campus\PaymentExpiryReminderMail;
use App\Models\CampusEnrollment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EnrollmentsRemind extends Command
{
    protected $signature = 'enrollments:remind
                            {--dry-run : Mostra quins correus s\'enviarien sense enviar-los}';

    protected $description = 'Envia recordatori 1 hora abans que caduqui una inscripció pendent de pagament manual.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Finestra: expiren entre 50 i 70 minuts → cobreix l'execució cada 15 min
        $candidates = CampusEnrollment::with(['student', 'course'])
            ->where('status', 'pending')
            ->whereNotNull('payment_expires_at')
            ->whereNull('reminder_sent_at')
            ->whereBetween('payment_expires_at', [now()->addMinutes(50), now()->addMinutes(70)])
            ->whereHas('course', fn ($q) => $q->whereNotNull('id')) // eager safety
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Cap inscripció pendent de recordatori.');
            return self::SUCCESS;
        }

        $this->info("Inscripcions a recordar: {$candidates->count()}");

        foreach ($candidates as $enrollment) {
            $email = $enrollment->student?->email ?? $enrollment->email;
            $name  = $enrollment->full_name;
            $exp   = $enrollment->payment_expires_at->format('d/m/Y H:i');

            if ($dryRun) {
                $this->line("  [dry-run] → {$name} <{$email}> | caduca {$exp}");
                continue;
            }

            Mail::to($email)->send(new PaymentExpiryReminderMail($enrollment));
            $enrollment->update(['reminder_sent_at' => now()]);
            $this->line("  ✓ Recordatori enviat → {$name} <{$email}>");
        }

        return self::SUCCESS;
    }
}
