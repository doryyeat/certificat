<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManagerCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $managerName,
        public string $email,
        public string $password,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Доступ менеджера GiftHub');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manager_credentials',
            with: [
                'managerName' => $this->managerName,
                'email' => $this->email,
                'password' => $this->password,
                'loginUrl' => $this->loginUrl,
            ]
        );
    }
}

