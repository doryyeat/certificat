<?php

namespace App\Services\Certificate;

use App\Models\Certificate;
use App\Models\Business;
use App\Models\Purchase;
use App\Services\Notification\EmailService;
use App\Services\Notification\PDFGenerator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CertificateGenerator
{
    protected EmailService $emailService;
    protected PDFGenerator $pdfGenerator;

    public function __construct(EmailService $emailService, PDFGenerator $pdfGenerator)
    {
        $this->emailService = $emailService;
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Генерирует новый сертификат после покупки
     */
    public function generate(Purchase $purchase, array $data): Certificate
    {
        $business = Business::find($data['business_id']);

        // Проверяем лимиты бизнеса
        if (!$business->canCreateCertificate()) {
            throw new \Exception('Certificate limit reached');
        }

        // Создаем сертификат
        $certificate = Certificate::create([
            'id' => $this->generateCertificateId(),
            'business_id' => $data['business_id'],
            'segment_id' => $data['segment_id'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'purchase_id' => $purchase->id,
            'nominal' => $data['nominal'],
            'balance' => $data['nominal'],
            'expires_at' => $this->calculateExpiryDate($data['expires_days'] ?? 365),
            'status' => 'active',
            'purchased_at' => now(),
        ]);

        // Генерируем QR код и PDF
        $this->generateQRCode($certificate);
        $this->generatePDF($certificate, $purchase->customer_email);

        // Создаем транзакцию
        $certificate->transactions()->create([
            'type' => 'purchase',
            'amount' => $certificate->nominal,
            'balance_before' => 0,
            'balance_after' => $certificate->nominal,
            'purchase_id' => $purchase->id,
        ]);

        // Отправляем email
        $this->sendCertificateEmail($certificate, $purchase->customer_email);

        return $certificate;
    }

    /**
     * Генерирует уникальный ID сертификата (CERT-001, CERT-002...)
     */
    protected function generateCertificateId(): string
    {
        $lastCertificate = Certificate::orderBy('id', 'desc')->first();

        if (!$lastCertificate) {
            return 'CERT-001';
        }

        $lastNumber = intval(substr($lastCertificate->id, 5));
        $newNumber = $lastNumber + 1;

        return 'CERT-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Рассчитывает дату истечения
     */
    protected function calculateExpiryDate(int $days): Carbon
    {
        return now()->addDays($days);
    }

    /**
     * Генерирует QR код для сертификата
     */
    protected function generateQRCode(Certificate $certificate): void
    {
        // TODO: Реализовать генерацию QR кода
        // Использовать QR-код с данными: certificate_id + pin_code
        $qrData = json_encode([
            'id' => $certificate->id,
            'pin' => $this->generatePinCode(),
        ]);

        // Сохраняем путь к QR коду
        $certificate->qr_code_path = "qrcodes/{$certificate->id}.png";
        $certificate->save();
    }

    /**
     * Генерирует PIN код для сертификата
     */
    protected function generatePinCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Генерирует PDF сертификата
     */
    protected function generatePDF(Certificate $certificate, string $email): void
    {
        $pdfPath = $this->pdfGenerator->generate($certificate);
        $certificate->pdf_path = $pdfPath;
        $certificate->save();
    }

    /**
     * Отправляет email с сертификатом
     */
    protected function sendCertificateEmail(Certificate $certificate, string $email): void
    {
        $this->emailService->sendCertificate($certificate, $email);
    }
}
