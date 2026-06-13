<?php

namespace App\Mail\Campus;

use App\Models\CampusStudent;
use App\Settings\SettingStore;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ManualPaymentPendingMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly CampusStudent $student,
        public readonly Collection    $enrollments,
        public readonly string        $method,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->enrollments->count() === 1
            ? 'Inscripció pendent de pagament — ' . $this->enrollments->first()->course->title
            : 'Inscripcions pendents de pagament (' . $this->enrollments->count() . ' cursos)';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $settings  = app(SettingStore::class);
        $reference = $this->enrollments->first()->payment_reference ?? '—';

        $courseTitles = $this->enrollments->count() === 1
            ? $this->enrollments->first()->course->title
            : $this->enrollments->map(fn($e) => $e->course->title)->join(', ');

        $concept = str_replace(
            ['{NOM}', '{CURS}', '{REFERENCIA}'],
            [$this->student->full_name, $courseTitles, $reference],
            $settings->get('payment_concept_template', '{NOM} - {CURS}'),
        );

        return new Content(
            markdown: 'emails.campus.manual-payment-pending',
            with: [
                'settings'  => $settings,
                'concept'   => $concept,
                'reference' => $reference,
            ],
        );
    }
}
