<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CampusStudent extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'campus_students';
    protected $guard = 'student';

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
        'phone', 'dni', 'address', 'postal_code', 'city', 'data_consent',
        'email_verified_at', 'suspended_at', 'suspension_reason',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'dni'               => 'encrypted',
        'data_consent'      => 'boolean',
        'password'          => 'hashed',
        'email_verified_at' => 'datetime',
        'suspended_at'      => 'datetime',
    ];

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /** Hash per a l'URL de verificació (SHA1 de l'email). */
    public function verificationHash(): string
    {
        return sha1($this->email);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CampusEnrollment::class, 'student_id');
    }

    public function lessonProgresses(): HasMany
    {
        return $this->hasMany(LmsLessonProgress::class, 'student_id');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            CampusCourse::class,
            'campus_course_student',
            'student_id',
            'course_id'
        )->withPivot('enrollment_id', 'enrolled_at')
         ->withTimestamps();
    }

    public function lmsResponses(): HasMany
    {
        return $this->hasMany(LmsLessonResponse::class, 'student_id');
    }

    public function lmsCertificates(): HasMany
    {
        return $this->hasMany(LmsCourseCertificate::class, 'student_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
