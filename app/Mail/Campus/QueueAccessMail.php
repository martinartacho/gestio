<?php

namespace App\Mail\Campus;

use App\Models\CampusQueueEntry;
use App\Settings\SettingStore;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Enviat quan arriba el torn: conté el codi d'accés i el temps disponible. */
class QueueAccessMail extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly CampusQueueEntry $entry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚀 Ara és el teu torn! Codi d\'accés — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        $windowMinutes = (int) app(SettingStore::class)->get('queue_access_window_minutes', 30);

        return new Content(
            markdown: 'emails.campus.queue-access',
            with: compact('windowMinutes'),
        );
    }
}
