<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Mail\CertificateRedeemedMail;
use App\Models\GiftCertificate;
use App\Models\GiftCertificateRedemption;
use App\Models\GiftCertificateTransaction;
use App\Models\PurchasedCertificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class RedeemController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Manager/Redeem/Index');
    }

    public function showByCode(Request $request, string $code): Response
    {
        $orgId = $request->user()->organization_id;
        $normalizedCode = strtoupper(trim($code));

        $certificate = PurchasedCertificate::query()
            ->where('organization_id', $orgId)
            ->where('code', $normalizedCode)
            ->with(['organization', 'store', 'transactions'])
            ->firstOrFail();

        return Inertia::render('Manager/Redeem/Show', [
            'certificate' => $certificate,
        ]);
    }

    public function redeem(Request $request, PurchasedCertificate $certificate): RedirectResponse
    {
        $orgId = $request->user()->organization_id;
        if ((int) $certificate->organization_id !== (int) $orgId) {
            abort(403);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $payload = DB::transaction(function () use ($certificate, $data, $request, $orgId) {
            $cert = PurchasedCertificate::query()->lockForUpdate()->findOrFail($certificate->id);

            if ($cert->status !== GiftCertificate::STATUS_ACTIVE) {
                abort(422, 'Сертификат недоступен для гашения.');
            }

            if (! $cert->sold_at) {
                abort(422, 'Сертификат ещё не куплен клиентом.');
            }

            if ($cert->expires_at && $cert->expires_at->isPast()) {
                $cert->status = GiftCertificate::STATUS_EXPIRED;
                $cert->save();
                abort(422, 'Срок действия сертификата истёк.');
            }

            if ((float) $data['amount'] > (float) $cert->balance) {
                abort(422, 'Сумма списания не может превышать остаток.');
            }

            $cert->balance = (float) $cert->balance - (float) $data['amount'];
            if ((float) $cert->balance <= 0) {
                $cert->status = GiftCertificate::STATUS_REDEEMED;
            }
            $cert->save();

            GiftCertificateRedemption::create([
                'gift_certificate_id' => $cert->id,
                'organization_id' => $orgId,
                'store_id' => $cert->store_id,
                'cashier_user_id' => $request->user()->id,
                'amount' => $data['amount'],
                'qr_data' => $cert->code,
                'verification_data' => [
                    'via' => 'manager',
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'redeemed_at' => now(),
            ]);

            GiftCertificateTransaction::create([
                'gift_certificate_id' => $cert->id,
                'type' => GiftCertificateTransaction::TYPE_REDEEM,
                'amount' => $data['amount'],
                'description' => 'Manager redeem',
            ]);

            return [
                'recipient_email' => $cert->recipient_email,
                'recipient_name' => $cert->recipient_name ?: 'Клиент',
                'certificate_code' => $cert->code,
                'amount_redeemed' => (float) $data['amount'],
                'balance_left' => (float) $cert->balance,
                'currency' => $cert->currency,
                'organization_name' => $cert->organization?->name,
            ];
        });

        if (!empty($payload['recipient_email'])) {
            Mail::to($payload['recipient_email'])->send(new CertificateRedeemedMail(
                recipientName: $payload['recipient_name'],
                certificateCode: $payload['certificate_code'],
                amountRedeemed: $payload['amount_redeemed'],
                balanceLeft: $payload['balance_left'],
                currency: $payload['currency'],
                organizationName: $payload['organization_name'] ?? 'GiftHub',
            ));
        }

        return back()->with('success', 'Списание выполнено.');
    }
}

