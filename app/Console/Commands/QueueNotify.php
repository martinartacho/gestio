<?php

namespace App\Console\Commands;

use App\Mail\Campus\QueueAccessMail;
use App\Models\CampusQueueEntry;
use App\Settings\SettingStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class QueueNotify extends Command
{
    protected $signature = 'queue:notify {--dry-run}';
    protected $description = 'Envia els codis d\'accés als torns de cua que han arribat.';

    public function handle(): int
    {
        $settings      = app(SettingStore::class);
        $windowMinutes = (int) $settings->get('queue_access_window_minutes', 30);

        if (! $settings->get('queue_enabled', false)) {
            $this->info('Cua desactivada.');
            return self::SUCCESS;
        }

        // Entrades el torn de les quals ha arribat però encara no s'ha notificat
        $candidates = CampusQueueEntry::where('status', CampusQueueEntry::STATUS_WAITING)
            ->where('slot_starts_at', '<=', now())
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Cap torn pendent de notificar.');
            return self::SUCCESS;
        }

        $this->info("Torns a notificar: {$candidates->count()}");

        foreach ($candidates as $entry) {
            if ($this->option('dry-run')) {
                $this->line("  [dry-run] #{$entry->queue_number} → {$entry->email}");
                continue;
            }

            $code       = CampusQueueEntry::generateCode();
            $expiresAt  = now()->addMinutes($windowMinutes);

            $entry->update([
                'access_code'       => $code,
                'notified_at'       => now(),
                'access_expires_at' => $expiresAt,
                'status'            => CampusQueueEntry::STATUS_NOTIFIED,
            ]);

            Mail::to($entry->email)->send(new QueueAccessMail($entry));
            $this->line("  ✓ #{$entry->queue_number} → {$entry->email} | codi: {$code}");
        }

        // Marcar com a expirats els torns notificats sense accés i caducats
        CampusQueueEntry::where('status', CampusQueueEntry::STATUS_NOTIFIED)
            ->where('access_expires_at', '<', now())
            ->update(['status' => CampusQueueEntry::STATUS_EXPIRED]);

        return self::SUCCESS;
    }
}
