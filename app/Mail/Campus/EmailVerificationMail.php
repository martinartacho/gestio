<?php

namespace App\Mail\Campus;

use App\Models\CampusStudent;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class EmailVerificationMail extends Mailable
{
    use SerializesModels;

    public readonly string $verificationUrl;

    public function __construct(public readonly CampusStudent $student)
    {
        // URL signada vàlida 72 hores
        $this->verificationUrl = URL::temporarySignedRoute(
            'campus.verification.verify',
            now()->addHours(72),
            ['id' => $student->id, 'hash' => $student->verificationHash()],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirma el teu correu electrònic — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campus.email-verification',
        );
    }
}
