<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\GiftCertificate;
use App\Models\Segment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    /**
     * Публичный поиск сертификатов (маркетплейс)
     *
     * GET /api/certificates?segment=horeca&lat=53.9&lng=27.55&radius=10
     */
    public function index(Request $request)
    {
        $request->validate([
            'segment' => 'nullable|string|exists:segments,slug',
            'lat' => 'required_with:lng|numeric',
            'lng' => 'required_with:lat|numeric',
            'radius' => 'nullable|integer|min:1|max:50',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Certificate::query()
            ->where('status', 'active')
            ->with(['business', 'location']);

        // Фильтр по сегменту
        if ($request->segment) {
            $segment = Segment::where('slug', $request->segment)->first();
            $query->where('segment_id', $segment->id);
        }

        // Фильтр по локации (радиус)
        if ($request->lat && $request->lng) {
            $radius = $request->radius ?? 10;

            $query->whereHas('location', function ($q) use ($request, $radius) {
                $q->inRadius($request->lat, $request->lng, $radius);
            });
        }

        // Сортировка
        $query->orderBy('created_at', 'desc');

        $certificates = $query->paginate($request->limit ?? 20);

        return response()->json([
            'data' => $certificates->map(function ($certificate) {
                return [
                    'id' => $certificate->id,
                    'business' => [
                        'id' => $certificate->business->id,
                        'name' => $certificate->business->name,
                        'verified' => $certificate->business->is_verified,
                    ],
                    'segment' => $certificate->segment?->name,
                    'nominal' => $certificate->nominal,
                    'location' => [
                        'address' => $certificate->location?->address,
                        'coordinates' => $certificate->location?->coordinates,
                    ],
                    'expires_at' => $certificate->expires_at,
                ];
            }),
            'meta' => [
                'total' => $certificates->total(),
                'per_page' => $certificates->perPage(),
                'current_page' => $certificates->currentPage(),
            ],
        ]);
    }

    /**
     * Получить детальную информацию о сертификате
     *
     * GET /api/certificates/{id}
     */
    public function show(string $id)
    {
        $certificate = GiftCertificate::with(['business', 'location', 'segment'])
            ->where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $certificate->id,
                'business' => [
                    'id' => $certificate->business->id,
                    'name' => $certificate->business->name,
                    'verified' => $certificate->business->is_verified,
                ],
                'segment' => $certificate->segment?->name,
                'nominal' => $certificate->nominal,
                'location' => [
                    'address' => $certificate->location?->address,
                    'coordinates' => $certificate->location?->coordinates,
                    'radius_km' => $certificate->location?->radius_km,
                ],
                'expires_at' => $certificate->expires_at,
                'purchased_at' => $certificate->purchased_at,
            ],
        ]);
    }
}
