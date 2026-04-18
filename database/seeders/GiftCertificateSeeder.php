<?php

namespace Database\Seeders;

use App\Models\GiftCertificate;
use App\Models\GiftCertificateTransaction;
use App\Models\Organization;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GiftCertificateSeeder extends Seeder
{
    public function run(): void
    {
        $organization = Organization::first();
        if (! $organization) {
            return;
        }

        $store = Store::where('organization_id', $organization->id)->first();
        if (! $store) {
            $store = Store::create([
                'organization_id' => $organization->id,
                'name' => 'Основная точка',
                'address' => 'г. Минск, ул. Примерная, д. 1',
                'phone' => '+375291234567',
            ]);
        }

        for ($i = 0; $i < 5; $i++) {
            $amount = rand(20, 150);
            $validity = rand(90, 365);

            $certificate = GiftCertificate::create([
                'organization_id' => $organization->id,
                'store_id' => $store->id,
                'code' => (function () {
                    $raw = strtoupper(bin2hex(random_bytes(8)));

                    return substr($raw, 0, 4).'-'.substr($raw, 4, 4).'-'.substr($raw, 8, 4).'-'.substr($raw, 12, 4);
                })(),
                'title' => 'Подарочный сертификат',
                'amount' => $amount,
                'balance' => $amount,
                'currency' => 'BYN',
                'category' => ['horeca', 'retail', 'services'][$i % 3],
                'validity_days' => $validity,
                'terms_of_use' => 'Действует при предъявлении в точке продаж.',
                'status' => GiftCertificate::STATUS_ACTIVE,
                'expires_at' => Carbon::now()->addDays($validity),
                'recipient_name' => 'Получатель '.($i + 1),
                'recipient_email' => "user{$i}@example.com",
                'notes' => 'Демо-сертификат GiftHub.',
            ]);

            GiftCertificateTransaction::create([
                'gift_certificate_id' => $certificate->id,
                'type' => GiftCertificateTransaction::TYPE_ISSUE,
                'amount' => $certificate->amount,
                'description' => 'Issue (seed)',
            ]);
        }
    }
}
