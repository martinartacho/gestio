<?php

namespace App\Mail\Associats;

use App\Models\AssociatMember;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MemberPasswordResetMail extends Mailable
{
    use SerializesModels;

    public readonly string $resetUrl;

    public function __construct(public readonly AssociatMember $member, string $token)
    {
        $this->resetUrl = route('member.password.reset', ['token' => $token]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperar contrasenya — ' . setting('associats_org_name', 'AC Granollers'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.associats.password-reset',
        );
    }
}
