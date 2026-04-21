<?php

namespace App\Services\Sertificate;

use App\Models\Certificate;
use App\Models\CertificateSplit;
use App\Services\Notification\EmailService;
use App\Services\Notification\PDFGenerator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CertificateSplitter
{
    protected EmailService $emailService;
    protected PDFGenerator $pdfGenerator;

    public function __construct(EmailService $emailService, PDFGenerator $pdfGenerator)
    {
        $this->emailService = $emailService;
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Разделяет сертификат между несколькими получателями
     */
    public function split(Certificate $certificate, array $data): array
    {
        // Проверяем возможность разделения
        $this->validateSplit($certificate, $data);

        return DB::transaction(function () use ($certificate, $data) {
            $splits = [];
            $totalAmount = array_sum(array_column($data['recipients'], 'amount'));

            // Вычитаем сумму из родительского сертификата
            $certificate->balance -= $totalAmount;

            if ($certificate->balance <= 0) {
                $certificate->status = 'used';
            }

            $certificate->save();

            // Создаем записи о разделении для каждого получателя
            foreach ($data['recipients'] as $recipient) {
                $split = CertificateSplit::create([
                    'parent_id' => $certificate->id,
                    'recipient_email' => $recipient['email'],
                    'recipient_name' => $recipient['name'] ?? null,
                    'amount' => $recipient['amount'],
                    'balance' => $recipient['amount'],
                    'access_token' => Str::random(64),
                    'pin_hash' => bcrypt($this->generatePinCode()),
                ]);

                // Генерируем QR код и PDF для разделенного сертификата
                $this->generateSplitQRCode($split);
                $this->generateSplitPDF($split);

                // Отправляем email получателю
                $this->emailService->sendSplitNotification($split);

                $splits[] = $split;
            }

            return $splits;
        });
    }

    /**
     * Проверяет возможность разделения
     */
    protected function validateSplit(Certificate $certificate, array $data): void
    {
        if ($certificate->status !== 'active') {
            throw new \Exception('Certificate is not active');
        }

        if ($certificate->balance <= 0) {
            throw new \Exception('Certificate has no balance');
        }

        $totalAmount = array_sum(array_column($data['recipients'], 'amount'));

        if ($totalAmount > $certificate->balance) {
            throw new \Exception('Total split amount exceeds certificate balance');
        }

        if (count($data['recipients']) > 5) {
            throw new \Exception('Maximum 5 recipients per split');
        }
    }

    /**
     * Генерирует PIN код для разделенного сертификата
     */
    protected function generatePinCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Генерирует QR код для разделения
     */
    protected function generateSplitQRCode(CertificateSplit $split): void
    {
        // TODO: Реализовать генерацию QR кода с токеном доступа
        $qrData = route('certificate.split.access', ['token' => $split->access_token]);

        // Сохраняем QR код
        $split->qr_path = "splits/qr_{$split->id}.png";
        $split->save();
    }

    /**
     * Генерирует PDF для разделения
     */
    protected function generateSplitPDF(CertificateSplit $split): void
    {
        $pdfPath = $this->pdfGenerator->generateSplit($split);
        $split->pdf_path = $pdfPath;
        $split->save();
    }
}
