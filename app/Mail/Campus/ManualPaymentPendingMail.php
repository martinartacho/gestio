<?php

namespace App\Mail\Campus;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusStudent;
use App\Settings\SettingStore;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManualPaymentPendingMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly CampusStudent    $student,
        public readonly CampusCourse     $course,
        public readonly CampusEnrollment $enrollment,
        public readonly string           $method,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inscripció pendent de pagament — ' . $this->course->title,
        );
    }

    public function content(): Content
    {
        $settings = app(SettingStore::class);

        $concept = str_replace(
            ['{NOM}', '{CURS}', '{REFERENCIA}'],
            [$this->student->full_name, $this->course->title, $this->enrollment->payment_reference ?? '—'],
            $settings->get('payment_concept_template', '{NOM} - {CURS}'),
        );

        return new Content(
            markdown: 'emails.campus.manual-payment-pending',
            with: [
                'settings' => $settings,
                'concept'  => $concept,
            ],
        );
    }
}
