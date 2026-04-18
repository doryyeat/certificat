<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\GiftCertificate;
use App\Models\GiftCertificateTransaction;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GiftCertificateController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $category = $request->string('category')->toString();

        $organizationId = $request->user()->organization_id;

        $query = GiftCertificate::query()
            ->with('store')
            ->when($organizationId, fn ($q) => $q->where('organization_id', $organizationId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('code', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%')
                        ->orWhere('terms_of_use', 'like', '%'.$search.'%')
                        ->orWhere('recipient_name', 'like', '%'.$search.'%')
                        ->orWhere('recipient_email', 'like', '%'.$search.'%');
                });
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderByDesc('created_at');

        return Inertia::render('GiftCertificates/Index', [
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category' => $category,
            ],
            'certificates' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(Request $request): Response
    {
        $organizationId = $request->user()->organization_id;

        $templates = CertificateTemplate::query()
            ->when($organizationId, fn ($q) => $q->where('organization_id', $organizationId))
            ->orderBy('name')
            ->get();

        $stores = Store::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();

        return Inertia::render('GiftCertificates/Create', [
            'templates' => $templates,
            'stores' => $stores,
            'requiresStore' => $stores->isEmpty(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organizationId = $request->user()->organization_id;

        $data = $request->validate([
            'template_id' => [
                'nullable',
                Rule::exists('certificate_templates', 'id')->where(
                    fn ($q) => $q->where('organization_id', $organizationId),
                ),
            ],
            'title' => ['nullable', 'string', 'max:200'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'currency' => ['required', 'string', 'size:3'],
            'validity_days' => ['required', 'integer', 'min:1', 'max:1095'],
            'category' => ['required', 'string', 'in:horeca,retail,services'],
            'terms_of_use' => ['nullable', 'string', 'max:1000'],
            'store_id' => ['required', 'exists:stores,id'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $store = Store::where('id', $data['store_id'])
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        $expiresAt = Carbon::now()->addDays($data['validity_days']);

        $code = $this->generateUniqueCertificateCode();

        $certificate = GiftCertificate::create([
            'organization_id' => $organizationId,
            'store_id' => $store->id,
            'template_id' => $data['template_id'] ?? null,
            'code' => $code,
            'title' => $data['title'] ?: 'Подарочный сертификат',
            'amount' => $data['amount'],
            'balance' => $data['amount'],
            'currency' => strtoupper($data['currency']),
            'category' => $data['category'],
            'validity_days' => $data['validity_days'],
            'terms_of_use' => $data['terms_of_use'] ?? null,
            'status' => GiftCertificate::STATUS_ACTIVE,
            'expires_at' => $expiresAt,
            'recipient_name' => $data['recipient_name'] ?? null,
            'recipient_email' => $data['recipient_email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        GiftCertificateTransaction::create([
            'gift_certificate_id' => $certificate->id,
            'type' => GiftCertificateTransaction::TYPE_ISSUE,
            'amount' => $certificate->amount,
            'description' => 'Issue',
        ]);

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Подарочный сертификат создан.');
    }

    public function show(Request $request, GiftCertificate $certificate): Response
    {
        $this->authorizeOrganization($request, $certificate);
        $certificate->load(['transactions', 'store']);

        return Inertia::render('GiftCertificates/Show', [
            'certificate' => $certificate,
        ]);
    }

    public function edit(Request $request, GiftCertificate $certificate): Response
    {
        $this->authorizeOrganization($request, $certificate);
        $organizationId = $request->user()->organization_id;

        $certificate->load(['transactions', 'store']);

        $stores = Store::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get();

        return Inertia::render('GiftCertificates/Edit', [
            'certificate' => $certificate,
            'stores' => $stores,
        ]);
    }

    public function update(Request $request, GiftCertificate $certificate): RedirectResponse
    {
        $this->authorizeOrganization($request, $certificate);

        $organizationId = $request->user()->organization_id;

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:200'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'balance' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'validity_days' => ['nullable', 'integer', 'min:1', 'max:1095'],
            'category' => ['required', 'string', 'in:horeca,retail,services'],
            'terms_of_use' => ['nullable', 'string', 'max:1000'],
            'store_id' => ['required', 'exists:stores,id'],
            'expires_at' => ['nullable', 'date'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        Store::where('id', $data['store_id'])
            ->where('organization_id', $organizationId)
            ->firstOrFail();

        $certificate->update([
            'title' => $data['title'] ?: $certificate->title,
            'amount' => $data['amount'],
            'balance' => $data['balance'],
            'currency' => strtoupper($data['currency']),
            'validity_days' => $data['validity_days'] ?? $certificate->validity_days,
            'category' => $data['category'],
            'terms_of_use' => $data['terms_of_use'] ?? null,
            'store_id' => $data['store_id'],
            'expires_at' => $data['expires_at'] ?? $certificate->expires_at,
            'recipient_name' => $data['recipient_name'] ?? null,
            'recipient_email' => $data['recipient_email'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('certificates.edit', $certificate)
            ->with('success', 'Сертификат обновлён.');
    }

    public function destroy(Request $request, GiftCertificate $certificate): RedirectResponse
    {
        $this->authorizeOrganization($request, $certificate);
        $certificate->delete();

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Сертификат удалён.');
    }

    public function redeem(Request $request, GiftCertificate $certificate): RedirectResponse
    {
        $this->authorizeOrganization($request, $certificate);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if ($certificate->status !== GiftCertificate::STATUS_ACTIVE) {
            return back()->withErrors([
                'amount' => 'Нельзя погасить сертификат в текущем статусе.',
            ]);
        }

        if ($certificate->expires_at && $certificate->expires_at->isPast()) {
            $certificate->status = GiftCertificate::STATUS_EXPIRED;
            $certificate->save();

            return back()->withErrors([
                'amount' => 'Срок действия сертификата истёк.',
            ]);
        }

        if ($data['amount'] > $certificate->balance) {
            return back()->withErrors([
                'amount' => 'Сумма превышает доступный баланс.',
            ]);
        }

        $certificate->balance -= $data['amount'];

        if ($certificate->balance <= 0) {
            $certificate->status = GiftCertificate::STATUS_REDEEMED;
        }

        $certificate->save();

        GiftCertificateTransaction::create([
            'gift_certificate_id' => $certificate->id,
            'type' => GiftCertificateTransaction::TYPE_REDEEM,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? 'Redeem',
        ]);

        return back()->with('success', 'Сертификат успешно погашен.');
    }

    private function authorizeOrganization(Request $request, GiftCertificate $certificate): void
    {
        $oid = $request->user()->organization_id;
        if (! $oid || (int) $certificate->organization_id !== (int) $oid) {
            abort(403);
        }
    }

    private function generateUniqueCertificateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $raw = '';
            for ($i = 0; $i < 16; $i++) {
                $raw .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = substr($raw, 0, 4).'-'.substr($raw, 4, 4).'-'.substr($raw, 8, 4).'-'.substr($raw, 12, 4);
        } while (GiftCertificate::where('code', $code)->exists());

        return $code;
    }
}
