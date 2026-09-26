<?php

namespace App\Mail\Campus;

use App\Support\Locales;
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
        $this->locale(Locales::resolve($teacher));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Recuperar contrasenya') . ' — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campus.teacher-password-reset',
        );
    }
}
