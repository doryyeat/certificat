<?php

namespace App\Mail;

use App\Models\GiftCertificate;
use App\Models\PurchasedCertificate;
use App\Services\Notification\PDFGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GiftCertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<GiftCertificate|PurchasedCertificate>  $certificates
     */
    public function __construct(
        public array $certificates,
        public string $recipientName,
        public ?string $messageText,
        public string $orderNumber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ваш подарочный сертификат',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.gift_certificates',
            with: [
                'certificates' => $this->certificates,
                'recipientName' => $this->recipientName,
                'messageText' => $this->messageText,
                'orderNumber' => $this->orderNumber,
            ],
        );
    }

    public function attachments(): array
    {
        /** @var PDFGenerator $pdf */
        $pdf = app(PDFGenerator::class);

        $attachments = [];
        foreach ($this->certificates as $certificate) {
            // QR ведет на страницу менеджера
            $qrPayload = route('manager.redeem.show', ['code' => $certificate->code], true);
            $qrPng = QrCode::format('png')->size(240)->margin(1)->generate($qrPayload);

            $pdfBytes = $pdf->generateGiftCertificatePdf($certificate, base64_encode($qrPng));

            $attachments[] = Attachment::fromData(
                fn () => $pdfBytes,
                'certificate_' . $certificate->code . '.pdf'
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}
