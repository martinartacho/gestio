<?php

namespace App\Mail\Campus;

use App\Support\Locales;
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
        $this->locale(Locales::resolve($student));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('El teu codi de verificació') . ' — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campus.email-verification',
        );
    }
}
