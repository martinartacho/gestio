<?php

namespace App\Mail\Campus;

use App\Models\CampusQueueEntry;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Enviat quan l'usuari s'apunta a la cua: confirma el número i l'hora estimada. */
class QueueConfirmationMail extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly CampusQueueEntry $entry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎟 Torn #' . $this->entry->queue_number . ' reservat — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.campus.queue-confirmation');
    }
}
