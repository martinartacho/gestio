<?php

namespace App\Mail\Campus;

use App\Models\CampusTeacher;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherPasswordResetMail extends Mailable
{
    use SerializesModels;

    public readonly string $otp;

    public function __construct(public readonly CampusTeacher $teacher)
    {
        $this->otp = $teacher->generateOtp();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperar contrasenya — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campus.teacher-password-reset',
        );
    }
}
