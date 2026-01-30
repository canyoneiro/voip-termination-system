<?php

namespace App\Mail;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Alert $alert,
    ) {}

    public function envelope(): Envelope
    {
        $severityEmoji = match ($this->alert->severity) {
            'critical' => '🚨',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            default => '📢',
        };

        return new Envelope(
            subject: sprintf(
                '%s [%s] %s',
                $severityEmoji,
                strtoupper($this->alert->severity),
                $this->alert->title
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alert',
            with: [
                'alert' => $this->alert,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
