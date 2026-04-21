<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\GiftCertificate;
use App\Models\GiftCertificateRedemption;
use App\Models\GiftCertificateTransaction;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PosRedemptionController extends Controller
{
    public function index(Request $request): Response
    {
        $organizationId = $request->user()->organization_id;

        $stores = Store::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get(['id', 'name', 'address']);

        return Inertia::render('Business/POS/Redeem', [
            'stores' => $stores,
        ]);
    }

    public function redeem(Request $request): RedirectResponse
    {
        $organizationId = $request->user()->organization_id;

        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'qr_data' => ['nullable', 'string', 'max:255'],
            'offline_id' => ['nullable', 'string', 'max:64'],
            'redeemed_at' => ['nullable', 'date'],
        ]);

        $store = Store::query()
            ->where('id', $data['store_id'])
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        DB::transaction(function () use ($data, $organizationId, $store, $request) {
            $certificate = GiftCertificate::query()
                ->where('organization_id', $organizationId)
                ->where('code', $data['code'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($certificate->status !== GiftCertificate::STATUS_ACTIVE) {
                abort(422, 'Сертификат недоступен для гашения.');
            }

            if (! $certificate->sold_at) {
                abort(422, 'Сертификат ещё не куплен клиентом.');
            }

            if ($certificate->expires_at && $certificate->expires_at->isPast()) {
                $certificate->status = GiftCertificate::STATUS_EXPIRED;
                $certificate->save();
                abort(422, 'Срок действия сертификата истёк.');
            }

            if ((float) $data['amount'] > (float) $certificate->balance) {
                abort(422, 'Сумма списания не может превышать остаток.');
            }

            $certificate->balance = (float) $certificate->balance - (float) $data['amount'];
            if ((float) $certificate->balance <= 0) {
                $certificate->status = GiftCertificate::STATUS_REDEEMED;
            }
            $certificate->save();

            GiftCertificateRedemption::create([
                'gift_certificate_id' => $certificate->id,
                'organization_id' => $organizationId,
                'store_id' => $store->id,
                'cashier_user_id' => $request->user()->id,
                'amount' => $data['amount'],
                'qr_data' => $data['qr_data'] ?? $certificate->code,
                'verification_data' => [
                    'store_id' => $store->id,
                    'offline_id' => $data['offline_id'] ?? null,
                    'redeemed_at_client' => $data['redeemed_at'] ?? null,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'redeemed_at' => isset($data['redeemed_at']) ? $data['redeemed_at'] : now(),
            ]);

            GiftCertificateTransaction::create([
                'gift_certificate_id' => $certificate->id,
                'type' => GiftCertificateTransaction::TYPE_REDEEM,
                'amount' => $data['amount'],
                'description' => 'POS redeem at ' . ($store->address ?? $store->name),
            ]);
        });

        return back()->with('success', 'Списание выполнено.');
    }
}

