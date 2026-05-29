<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class AssociatMember extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'associat_members';
    protected $guard = 'member';

    protected $fillable = [
        'member_number', 'first_name', 'last_name', 'email', 'password',
        'phone', 'dni', 'address', 'postal_code', 'city',
        'status', 'joined_at', 'cancelled_at', 'data_consent',
        'qr_token', 'campus_student_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'dni'          => 'encrypted',
        'password'     => 'hashed',
        'data_consent' => 'boolean',
        'joined_at'    => 'date',
        'cancelled_at' => 'date',
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
}
