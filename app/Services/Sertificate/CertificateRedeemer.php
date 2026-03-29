<?php

namespace App\Services\Certificate;

use App\Models\Certificate;
use App\Models\CertificateRedemption;
use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CertificateRedeemer
{
    /**
     * Гасит сертификат (списывает сумму)
     */
    public function redeem(array $data, Business $business): CertificateRedemption
    {
        return DB::transaction(function () use ($data, $business) {
            // Находим сертификат
            $certificate = $this->findCertificate($data);

            // Проверяем возможность гашения
            $this->validateRedemption($certificate, $data, $business);

            // Списываем сумму
            $oldBalance = $certificate->balance;
            $certificate->balance -= $data['amount'];

            if ($certificate->balance <= 0) {
                $certificate->status = 'used';
                $certificate->redeemed_at = now();
            }

            $certificate->save();

            // Создаем запись о гашении
            $redemption = CertificateRedemption::create([
                'certificate_id' => $certificate->id,
                'business_id' => $business->id,
                'location_id' => $data['location_id'] ?? null,
                'amount' => $data['amount'],
                'qr_data' => $data['qr_data'] ?? null,
                'pin_code' => $data['pin_code'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'redeemed_at' => now(),
            ]);

            // Создаем транзакцию
            $certificate->transactions()->create([
                'type' => 'redeem',
                'amount' => $data['amount'],
                'balance_before' => $oldBalance,
                'balance_after' => $certificate->balance,
                'metadata' => ['redemption_id' => $redemption->id],
            ]);

            return $redemption;
        });
    }

    /**
     * Находит сертификат по QR данным или PIN коду
     */
    protected function findCertificate(array $data): Certificate
    {
        if (isset($data['qr_data'])) {
            $qrData = json_decode($data['qr_data'], true);

            if (isset($qrData['id'])) {
                return Certificate::findOrFail($qrData['id']);
            }
        }

        // TODO: Поиск по PIN коду (с проверкой хеша)
        throw new \Exception('Certificate not found');
    }

    /**
     * Проверяет возможность гашения
     */
    protected function validateRedemption(Certificate $certificate, array $data, Business $business): void
    {
        // Проверяем статус
        if ($certificate->status !== 'active') {
            throw new \Exception('Certificate is not active');
        }

        // Проверяем срок действия
        if ($certificate->expires_at->isPast()) {
            $certificate->update(['status' => 'expired']);
            throw new \Exception('Certificate has expired');
        }

        // Проверяем баланс
        if ($certificate->balance < $data['amount']) {
            throw new \Exception('Insufficient balance');
        }

        // Проверяем PIN код если есть
        if (isset($data['pin_code'])) {
            // TODO: Проверить PIN код
        }

        // Проверяем локацию если бизнес использует гео-ограничения
        if ($data['location_id'] ?? null) {
            $this->validateLocation($certificate, $data['location_id']);
        }
    }

    /**
     * Проверяет, что гашение происходит в разрешенной локации
     */
    protected function validateLocation(Certificate $certificate, int $locationId): void
    {
        $location = Location::find($locationId);

        if (!$location) {
            throw new \Exception('Location not found');
        }

        // Если у сертификата есть привязка к конкретной локации
        if ($certificate->location_id && $certificate->location_id !== $location->id) {
            throw new \Exception('Certificate can only be redeemed at the specified location');
        }

        // TODO: Проверить радиус, если бизнес использует гео-зону
    }
}
