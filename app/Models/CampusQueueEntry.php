<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CampusQueueEntry extends Model
{
    protected $table = 'campus_queue_entries';

    protected $fillable = [
        'email', 'queue_number', 'access_code',
        'slot_starts_at', 'notified_at', 'accessed_at', 'access_expires_at', 'status',
    ];

    protected $casts = [
        'slot_starts_at'    => 'datetime',
        'notified_at'       => 'datetime',
        'accessed_at'       => 'datetime',
        'access_expires_at' => 'datetime',
    ];

    public const STATUS_WAITING  = 'waiting';
    public const STATUS_NOTIFIED = 'notified';
    public const STATUS_ACCESSED = 'accessed';
    public const STATUS_EXPIRED  = 'expired';

    /**
     * Calcula la posició del torn (0-indexat) a partir del número de cua.
     * slot_index = ceil(queue_number / batch_size) - 1
     */
    public static function slotIndexFor(int $queueNumber, int $batchSize): int
    {
        return (int) (ceil($queueNumber / max(1, $batchSize)) - 1);
    }

    /** Genera un codi OTP de 6 caràcters (3 dígits + 3 lletres). */
    public static function generateCode(): string
    {
        $digits  = (string) random_int(100, 999);
        $letters = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ'), 0, 3));
        return $digits . $letters;
    }

    /** Retorna l'índex del slot actual de l'entrada (útil per a la vista de canvi de torn). */
    public function getCurrentSlotIndexAttribute(): int
    {
        $batchSize = max(1, (int) app(\App\Settings\SettingStore::class)->get('queue_batch_size', 10));
        return static::slotIndexFor($this->queue_number, $batchSize);
    }

    /** Retorna true si el codi és vàlid i el torn és actiu. */
    public function canAccess(): bool
    {
        return $this->status === self::STATUS_NOTIFIED
            && $this->access_expires_at?->isFuture();
    }

    /** Retorna true si ja ha accedit. */
    public function hasAccessed(): bool
    {
        return $this->status === self::STATUS_ACCESSED;
    }

    /** Hora estimada formatada per a l'usuari. */
    public function slotTimeLabel(): string
    {
        // Slot ja obert → notificació imminent quan s'executi el scheduler
        if ($this->slot_starts_at->isPast()) {
            return 'Molt aviat';
        }
        if ($this->slot_starts_at->isToday()) {
            return 'avui a les ' . $this->slot_starts_at->format('H:i') . ' h';
        }
        if ($this->slot_starts_at->isTomorrow()) {
            return 'demà a les ' . $this->slot_starts_at->format('H:i') . ' h';
        }
        return $this->slot_starts_at->format('d/m/Y') . ' a les ' . $this->slot_starts_at->format('H:i') . ' h';
    }
}
