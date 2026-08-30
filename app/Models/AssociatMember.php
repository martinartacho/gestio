<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class AssociatMember extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant;

    protected $table = 'associat_members';
    protected $guard = 'member';

    protected $fillable = [
        'tenant_id',
        'member_number', 'first_name', 'last_name', 'email', 'password',
        'phone', 'dni', 'address', 'postal_code', 'city',
        'status', 'joined_at', 'cancelled_at', 'data_consent',
        'qr_token', 'campus_student_id',
        'reset_token', 'reset_token_expires_at',
        'bank_iban', 'bank_holder',
        'mandate_reference', 'mandate_signed_at', 'mandate_sequence',
        'mandate_method', 'mandate_document', 'mandate_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'dni'                    => 'encrypted',
        'bank_iban'              => 'encrypted',
        'password'               => 'hashed',
        'data_consent'           => 'boolean',
        'joined_at'              => 'date',
        'cancelled_at'           => 'date',
        'mandate_signed_at'      => 'date',
        'reset_token_expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $member) {
            if (empty($member->qr_token)) {
                $member->qr_token = Str::uuid()->toString();
            }
        });
    }

    public function campusStudent(): BelongsTo
    {
        return $this->belongsTo(CampusStudent::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function generateResetToken(): string
    {
        $token = \Illuminate\Support\Str::uuid()->toString();

        $this->update([
            'reset_token'            => $token,
            'reset_token_expires_at' => now()->addHour(),
        ]);

        return $token;
    }

    public function isValidResetToken(string $token): bool
    {
        return $this->reset_token === $token
            && $this->reset_token_expires_at
            && $this->reset_token_expires_at->isFuture();
    }

    public function clearResetToken(): void
    {
        $this->update([
            'reset_token'            => null,
            'reset_token_expires_at' => null,
        ]);
    }
}
