<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Ramsey\Uuid\Uuid;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    private User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'API Sample Welcome Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $validationToken = Uuid::fromBytes($this->user->email_verification_token)->toString();

        return new Content(
            view: 'emails.welcome',
            with: [
                'validationToken' => $validationToken,
                'emailAddress' => $this->user->email,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
