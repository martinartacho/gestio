<?php

namespace App\Mail\Campus;

use App\Models\CampusEnrollment;
use App\Settings\SettingStore;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentExpiryReminderMail extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly CampusEnrollment $enrollment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏰ Recordatori: pagament pendent — ' . $this->enrollment->course->title,
        );
    }

    public function content(): Content
    {
        $settings = app(SettingStore::class);
        $enrollment = $this->enrollment;
        $course     = $enrollment->course;
        $student    = $enrollment->student;

        $concept = str_replace(
            ['{NOM}', '{CURS}', '{REFERENCIA}'],
            [$student->full_name, $course->title, $enrollment->payment_reference ?? '—'],
            $settings->get('payment_concept_template', '{NOM} - {CURS}'),
        );

        return new Content(
            markdown: 'emails.campus.payment-expiry-reminder',
            with: compact('settings', 'concept'),
        );
    }
}
