<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedIp extends Model
{
    protected $table = 'campus_blocked_ips';

    protected $fillable = ['ip', 'reason', 'blocked_by', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /** Retorna true si el bloqueig continua actiu. */
    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    /** Comprova si una IP concreta està bloquejada (activa). */
    public static function isBlocked(string $ip): bool
    {
        return static::where('ip', $ip)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
