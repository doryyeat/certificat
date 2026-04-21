<?php

namespace App\Services\Notification;

use App\Models\GiftCertificate;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class PDFGenerator
{
    public function generateGiftCertificatePdf(GiftCertificate $certificate, string $qrPngBase64): string
    {
        $org = $certificate->organization;

        $branding = [
            'plan' => $org?->planKey() ?? 'free',
            'colors' => [],
            'logoBase64' => null,
            'backgroundBase64' => null,
        ];

        try {
            if ($org && $org->brandingAllowed()) {
                $colors = array_values(array_filter($org->branding_colors ?? [], fn ($c) => is_string($c) && $c !== ''));
                $max = $org->brandingMaxColors();
                $branding['colors'] = array_slice($colors, 0, $max);

                if ($org->branding_logo_path && Storage::disk('public')->exists($org->branding_logo_path)) {
                    $raw = Storage::disk('public')->get($org->branding_logo_path);
                    $ext = strtolower(pathinfo($org->branding_logo_path, PATHINFO_EXTENSION));
                    $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                    $branding['logoBase64'] = "data:$mime;base64," . base64_encode($raw);
                }

                if ($org->brandingBackgroundAllowed() && $org->branding_background_path && Storage::disk('public')->exists($org->branding_background_path)) {
                    $raw = Storage::disk('public')->get($org->branding_background_path);
                    $ext = strtolower(pathinfo($org->branding_background_path, PATHINFO_EXTENSION));
                    $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                    $branding['backgroundBase64'] = "data:$mime;base64," . base64_encode($raw);
                }
            }
        } catch (\Throwable $e) {
            // По ТЗ: при ошибках брендирования отправляем стандартный Free-PDF, факт фиксируем в журнале
            Log::warning('Branding apply failed, falling back to free', [
                'certificate_id' => $certificate->id,
                'org_id' => $org?->id,
                'error' => $e->getMessage(),
            ]);
            $branding = [
                'plan' => 'free',
                'colors' => [],
                'logoBase64' => null,
                'backgroundBase64' => null,
            ];
        }

        $html = View::make('certificates.gift_certificate_pdf', [
            'certificate' => $certificate,
            'qrPngBase64' => $qrPngBase64,
            'branding' => $branding,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }
}
