<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificateRedeemedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $certificateCode,
        public float $amountRedeemed,
        public float $balanceLeft,
        public string $currency,
        public string $organizationName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Списание по вашему подарочному сертификату',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate_redeemed',
            with: [
                'recipientName' => $this->recipientName,
                'certificateCode' => $this->certificateCode,
                'amountRedeemed' => $this->amountRedeemed,
                'balanceLeft' => $this->balanceLeft,
                'currency' => $this->currency,
                'organizationName' => $this->organizationName,
            ]
        );
    }
}

