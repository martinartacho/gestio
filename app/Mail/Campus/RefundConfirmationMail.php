<?php

namespace App\Mail\Campus;

use App\Models\CampusEnrollment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundConfirmationMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly CampusEnrollment $enrollment,
        public readonly bool $isStripe,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '↩ Devolució confirmada — ' . $this->enrollment->course->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campus.refund-confirmation',
        );
    }
}
