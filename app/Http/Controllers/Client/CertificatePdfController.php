<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GiftCertificate;
use App\Models\Order;
use App\Services\Notification\PDFGenerator;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificatePdfController extends Controller
{
    public function download(Request $request, GiftCertificate $certificate, PDFGenerator $pdf)
    {
        $order = Order::query()
            ->where('id', $certificate->sold_order_id)
            ->where('user_id', $request->user()->id)
            ->where('status', Order::STATUS_PAID)
            ->first();

        if (! $order) {
            abort(403);
        }

        $qrPng = QrCode::format('png')->size(240)->margin(1)->generate($certificate->code);
        $bytes = $pdf->generateGiftCertificatePdf($certificate->loadMissing(['organization', 'store']), base64_encode($qrPng));

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="certificate_' . $certificate->code . '.pdf"',
        ]);
    }
}

