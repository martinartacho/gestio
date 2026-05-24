<?php

namespace App\Mail\Campus;

use App\Models\CampusStudent;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use SerializesModels;

    public readonly string $otp;

    public function __construct(public readonly CampusStudent $student)
    {
        $this->otp = $student->generateOtp();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'El teu codi de verificació — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campus.email-verification',
        );
    }
}
