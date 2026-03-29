<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificate\CreateCertificateRequest;
use App\Http\Requests\Certificate\SplitCertificateRequest;
use App\Http\Requests\Certificate\RedeemCertificateRequest;
use App\Models\Certificate;
use App\Models\GiftCertificate;
use App\Services\Certificate\CertificateGenerator;
use App\Services\Certificate\CertificateSplitter;
use App\Services\Certificate\CertificateRedeemer;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    protected CertificateGenerator $generator;
    protected CertificateSplitter $splitter;
    protected CertificateRedeemer $redeemer;

    public function __construct(
        CertificateGenerator $generator,
        CertificateSplitter $splitter,
        CertificateRedeemer $redeemer
    ) {
        $this->generator = $generator;
        $this->splitter = $splitter;
        $this->redeemer = $redeemer;
    }

    /**
     * Создать новый сертификат (для продажи)
     *
     * POST /api/business/certificates
     */
    public function store(CreateCertificateRequest $request)
    {
        $business = $request->user()->business;

        // Создаем запись о покупке
        $purchase = DB::transaction(function () use ($request, $business) {
            $purchase = Purchase::create([
                'payment_id' => 'PURCHASE-' . uniqid(),
                'business_id' => $business->id,
                'amount' => $request->nominal,
                'commission' => $request->nominal * ($business->subscription_details['commission_rate'] / 100),
                'status' => 'pending',
            ]);

            return $purchase;
        });

        // Генерируем сертификат
        $certificate = $this->generator->generate($purchase, [
            'business_id' => $business->id,
            'segment_id' => $request->segment_id,
            'location_id' => $request->location_id,
            'nominal' => $request->nominal,
            'expires_days' => $request->expires_days ?? 365,
        ]);

        return response()->json([
            'message' => 'Certificate created successfully',
            'data' => [
                'id' => $certificate->id,
                'nominal' => $certificate->nominal,
                'balance' => $certificate->balance,
                'expires_at' => $certificate->expires_at,
                'qr_code' => $certificate->qr_url,
                'pdf' => $certificate->pdf_url,
            ],
        ], 201);
    }

    /**
     * Разделить сертификат (SmartShare)
     *
     * POST /api/business/certificates/split
     */
    public function split(SplitCertificateRequest $request, Certificate $certificate)
    {
        $splits = $this->splitter->split($certificate, $request->validated());

        return response()->json([
            'message' => 'Certificate split successfully',
            'data' => [
                'parent' => [
                    'id' => $certificate->id,
                    'new_balance' => $certificate->balance,
                ],
                'splits' => collect($splits)->map(function ($split) {
                    return [
                        'id' => $split->id,
                        'recipient_email' => $split->recipient_email,
                        'amount' => $split->amount,
                        'access_url' => $split->access_url,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Погасить сертификат (списать сумму)
     *
     * POST /api/business/certificates/{id}/redeem
     */
    public function redeem(RedeemCertificateRequest $request, string $id)
    {
        $redemption = $this->redeemer->redeem(
            array_merge($request->validated(), ['certificate_id' => $id]),
            $request->user()->business
        );

        return response()->json([
            'message' => 'Certificate redeemed successfully',
            'data' => [
                'certificate_id' => $redemption->certificate_id,
                'amount' => $redemption->amount,
                'new_balance' => $redemption->certificate->balance,
                'redeemed_at' => $redemption->redeemed_at,
            ],
        ]);
    }

    /**
     * Получить список сертификатов бизнеса
     *
     * GET /api/business/certificates
     */
    public function index(Request $request)
    {
        $business = $request->user()->business;

        $certificates = GiftCertificate::where('business_id', $business->id)
            ->with(['location', 'segment'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->segment, function ($query, $segment) {
                return $query->whereHas('segment', function ($q) use ($segment) {
                    $q->where('slug', $segment);
                });
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_dir ?? 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $certificates->map(function ($certificate) {
                return [
                    'id' => $certificate->id,
                    'nominal' => $certificate->nominal,
                    'balance' => $certificate->balance,
                    'status' => $certificate->status,
                    'usage_percentage' => $certificate->usage_percentage,
                    'segment' => $certificate->segment?->name,
                    'location' => $certificate->location?->address,
                    'created_at' => $certificate->created_at,
                    'expires_at' => $certificate->expires_at,
                ];
            }),
            'meta' => [
                'total' => $certificates->total(),
                'total_nominal' => $certificates->sum('nominal'),
                'total_balance' => $certificates->sum('balance'),
            ],
        ]);
    }
}
