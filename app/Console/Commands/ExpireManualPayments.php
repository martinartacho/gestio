<?php

namespace App\Console\Commands;

use App\Models\CampusEnrollment;
use Illuminate\Console\Command;

class ExpireManualPayments extends Command
{
    protected $signature   = 'enrollments:expire {--dry-run : Mostra quines matrícules caducarien sense modificar-les}';
    protected $description = 'Cancel·la les matrícules de pagament manual que han superat el termini sense confirmar.';

    public function handle(): int
    {
        $query = CampusEnrollment::where('status', 'pending')
            ->whereNotNull('payment_expires_at')
            ->where('payment_expires_at', '<', now())
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', 'stripe');

        $count = $query->count();

        if ($count === 0) {
            $this->info('Cap matrícula pendent caducada.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("--dry-run: {$count} matrícules caducarien (sense modificar):");
            $query->with('course:id,title')->each(function ($e) {
                $this->line("  · #{$e->id} {$e->full_name} — {$e->course?->title} (exp. {$e->payment_expires_at->format('d/m/Y')})");
            });
            return self::SUCCESS;
        }

        $query->update(['status' => 'cancelled']);

        $this->info("✓ {$count} matrícula(es) marcades com a cancel·lades per caducitat.");
        return self::SUCCESS;
    }
}
