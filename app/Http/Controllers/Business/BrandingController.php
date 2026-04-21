<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Services\Notification\PDFGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Inertia\Inertia;
use Inertia\Response;

class BrandingController extends Controller
{
    public function show(Request $request): Response
    {
        $org = $request->user()->organization;

        return Inertia::render('Business/Branding/Show', [
            'plan' => $org?->planKey() ?? 'free',
            'maxColors' => $org?->brandingMaxColors() ?? 0,
            'backgroundAllowed' => (bool) ($org?->brandingBackgroundAllowed() ?? false),
            'branding' => [
                'logo_url' => $org?->branding_logo_path ? Storage::disk('public')->url($org->branding_logo_path) : null,
                'background_url' => $org?->branding_background_path ? Storage::disk('public')->url($org->branding_background_path) : null,
                'colors' => $org?->branding_colors ?? [],
            ],
        ]);
    }

    public function update(Request $request)
    {
        $org = $request->user()->organization;
        if (! $org) {
            abort(404);
        }

        $maxColors = $org->brandingMaxColors();
        $backgroundAllowed = $org->brandingBackgroundAllowed();

        $data = $request->validate([
            'colors' => ['nullable', 'array', 'max:3'],
            'colors.*' => ['nullable', 'string', 'max:20', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'background' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg',
                'max:10240',
            ],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_background' => ['nullable', 'boolean'],
        ]);

        // Ограничение цветов по тарифу: Free=0, Start=1, Pro=3
        $colors = array_values(array_filter($data['colors'] ?? [], fn ($c) => is_string($c) && $c !== ''));
        if (count($colors) > $maxColors) {
            $colors = array_slice($colors, 0, $maxColors);
        }
        if ($maxColors === 0) {
            $colors = [];
        }

        // Загружаем файлы, но применяться к новым PDF они будут только на Start/Pro (в PDFGenerator)
        if (!empty($data['remove_logo'])) {
            if ($org->branding_logo_path) {
                Storage::disk('public')->delete($org->branding_logo_path);
            }
            $org->branding_logo_path = null;
        }

        if ($request->hasFile('logo')) {
            if ($org->branding_logo_path) {
                Storage::disk('public')->delete($org->branding_logo_path);
            }
            $org->branding_logo_path = $request->file('logo')->store('branding/'.$org->id, 'public');
        }

        if (!empty($data['remove_background'])) {
            if ($org->branding_background_path) {
                Storage::disk('public')->delete($org->branding_background_path);
            }
            $org->branding_background_path = null;
        }

        if ($request->hasFile('background')) {
            if (! $backgroundAllowed) {
                return back()->withErrors(['background' => 'Фоновое изображение доступно только на тарифе Pro.']);
            }
            if ($org->branding_background_path) {
                Storage::disk('public')->delete($org->branding_background_path);
            }
            $org->branding_background_path = $request->file('background')->store('branding/'.$org->id, 'public');
        }

        $org->branding_colors = $colors;
        $org->save();

        return redirect()->route('business.branding.show')->with('success', 'Брендирование сохранено.');
    }

    public function preview(Request $request, PDFGenerator $pdf)
    {
        $org = $request->user()->organization;
        if (! $org) {
            abort(404);
        }

        try {
            // Превью делаем на "виртуальном" сертификате, чтобы не зависеть от наличия реальных
            $fake = new \App\Models\GiftCertificate([
                'code' => 'AB3F-9K2L-MN7P-QW4R',
                'title' => 'Подарочный сертификат',
                'amount' => 50,
                'balance' => 50,
                'currency' => 'BYN',
                'category' => \App\Models\GiftCertificate::CATEGORY_HORECA,
                'expires_at' => now()->addDays(90),
                'terms_of_use' => 'Условия использования будут отображаться здесь.',
                'recipient_name' => 'Получатель',
                'recipient_email' => 'recipient@example.by',
                'notes' => 'Персональное сообщение от дарителя.',
            ]);
            $fake->setRelation('organization', $org);
            $fake->setRelation('store', $org->stores()->first());

            $qrPng = QrCode::format('png')->size(240)->margin(1)->generate($fake->code);
            $bytes = $pdf->generateGiftCertificatePdf($fake, base64_encode($qrPng));

            return response($bytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="branding_preview.pdf"',
            ]);
        } catch (\Throwable $e) {
            Log::error('Branding preview failed', ['org_id' => $org->id, 'error' => $e->getMessage()]);
            abort(500, 'Не удалось построить предпросмотр PDF.');
        }
    }
}

