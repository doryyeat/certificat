<?php

namespace Database\Seeders;

use App\Models\GiftCertificate;
use App\Models\GiftCertificateTransaction;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GiftCertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();

        for ($i = 0; $i < 5; $i++) {
            $amount = rand(1000, 5000);

            $certificate = GiftCertificate::create([
                'organization_id' => $organization?->id,
                'code' => Str::upper(Str::random(12)),
                'amount' => $amount,
                'balance' => $amount,
                'currency' => 'RUB',
                'status' => GiftCertificate::STATUS_ACTIVE,
                'recipient_name' => 'Получатель ' . ($i + 1),
                'recipient_email' => "user{$i}@example.com",
                'notes' => 'Демо-сертификат для примера.',
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

