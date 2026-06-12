<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusCart extends Model
{
    protected $table    = 'campus_carts';
    protected $fillable = ['student_id', 'expires_at'];
    protected $casts    = ['expires_at' => 'datetime'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(CampusStudent::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CampusCartItem::class, 'cart_id');
    }

    public function total(): float
    {
        return (float) $this->items->sum('price');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Retorna el carret actiu de l'alumne, o en crea un de nou. */
    public static function forStudent(CampusStudent $student): self
    {
        $cart = self::where('student_id', $student->id)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()
            ->first();

        if (! $cart) {
            $cart = self::create([
                'student_id' => $student->id,
                'expires_at' => now()->addMinutes(30),
            ]);
        }

        return $cart;
    }
}
