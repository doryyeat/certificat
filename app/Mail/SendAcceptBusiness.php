<?php

namespace App\Mail;

use App\Models\RegisterRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendAcceptBusiness extends Mailable
{
    use Queueable, SerializesModels;

    protected string $mailTo;
    protected RegisterRequest $registerRequest;
    /**
     * Create a new message instance.
     */
    public function __construct(string $mailTo,RegisterRequest $registerRequest)
    {
        $this->mailTo = $mailTo;
        $this->registerRequest = $registerRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('rovik636@gmail.com','Gift Hub'),
            replyTo: $this->mailTo,
            subject: 'Send Accept Business',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.accept_business',
            with: [
                'registerRequest' => $this->registerRequest,
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
