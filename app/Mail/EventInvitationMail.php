<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\EventStaff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly EventStaff $eventStaff,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited: {$this->eventStaff->event->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
        $rsvpUrl     = "{$frontendUrl}/rsvp/{$this->eventStaff->invitation_token}";

        return new Content(
            view: 'emails.event-invitation',
            with: [
                'staffName'  => $this->eventStaff->staff->name,
                'eventTitle' => $this->eventStaff->event->title,
                'eventDate'  => $this->eventStaff->event->event_date->format('d F Y'),
                'organizer'  => $this->eventStaff->event->organizer->name,
                'rsvpUrl'    => $rsvpUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
