<?php

namespace App\Mail\Campus;

use App\Models\CampusStudent;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use SerializesModels;

    public readonly string $otp;

    public function __construct(public readonly CampusStudent $student)
    {
        // Reutilitzem els camps verification_code per al reset de contrasenya
        $this->otp = $student->generateOtp();
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
            markdown: 'emails.campus.password-reset',
        );
    }
}
