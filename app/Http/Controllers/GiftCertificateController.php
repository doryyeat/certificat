<?php

namespace App\Http\Controllers;

use App\Models\GiftCertificate;
use App\Models\GiftCertificateTransaction;
use App\Models\CertificateTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GiftCertificateController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();

        $organizationId = $request->user()?->organization_id;

        $query = GiftCertificate::query()
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->when($search, function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('recipient_name', 'like', '%' . $search . '%')
                    ->orWhere('recipient_email', 'like', '%' . $search . '%');
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderByDesc('created_at');

        return Inertia::render('GiftCertificates/Index', [
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'certificates' => $query->paginate(10)->withQueryString(),
        ]);
    }

    public function create(): Response
    {
        $templates = CertificateTemplate::query()
            ->when(auth()->user()?->organization_id, fn($q) => $q->where('organization_id', auth()->user()->organization_id))
            ->orderBy('name')
            ->get();

        return Inertia::render('GiftCertificates/Create', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'template_id' => ['nullable', 'exists:certificate_templates,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'expires_at' => ['nullable', 'date'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $code = Str::upper(Str::random(12));

        $organizationId = $request->user()?->organization_id;

        $certificate = GiftCertificate::create([
            'organization_id' => $organizationId,
            'template_id' => $data['template_id'] ?? null,
            'code' => $code,
            'amount' => $data['amount'],
            'balance' => $data['amount'],
            'currency' => $data['currency'],
            'status' => GiftCertificate::STATUS_ACTIVE,
            'expires_at' => $data['expires_at'] ?? null,
            'recipient_name' => $data['recipient_name'] ?? null,
            'recipient_email' => $data['recipient_email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()?->id,
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

    public function show(GiftCertificate $certificate): Response
    {
        $certificate->load('transactions');

        return Inertia::render('GiftCertificates/Show', [
            'certificate' => $certificate,
        ]);
    }

    public function edit(GiftCertificate $certificate): Response
    {
        $certificate->load('transactions');

        return Inertia::render('GiftCertificates/Edit', [
            'certificate' => $certificate,
        ]);
    }

    public function update(Request $request, GiftCertificate $certificate): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'balance' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'expires_at' => ['nullable', 'date'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $certificate->update($data);

        return redirect()
            ->route('certificates.edit', $certificate)
            ->with('success', 'Сертификат обновлён.');
    }

    public function destroy(GiftCertificate $certificate): RedirectResponse
    {
        $certificate->delete();

        return redirect()
            ->route('certificates.index')
            ->with('success', 'Сертификат удалён.');
    }

    public function redeem(Request $request, GiftCertificate $certificate): RedirectResponse
    {
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
}

