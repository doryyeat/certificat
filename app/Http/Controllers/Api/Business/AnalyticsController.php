<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateRedemption;
use App\Models\CertificateSplit;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Получить аналитику для бизнеса
     *
     * GET /api/business/analytics
     */
    public function index(Request $request)
    {
        $business = $request->user()->business;
        $period = $request->period ?? 'month';
        $dateRange = $this->getDateRange($period);

        // Продажи
        $sales = $this->getSalesAnalytics($business, $dateRange);

        // Гашения
        $redemptions = $this->getRedemptionAnalytics($business, $dateRange);

        // SmartShare статистика
        $smartShare = $this->getSmartShareAnalytics($business, $dateRange);

        // Топ локации
        $topLocations = $this->getTopLocations($business, $dateRange);

        return response()->json([
            'data' => [
                'sales' => $sales,
                'redemptions' => $redemptions,
                'smart_share' => $smartShare,
                'top_locations' => $topLocations,
                'summary' => $this->getSummaryStats($business, $dateRange),
            ]
        ]);
    }

    /**
     * Аналитика продаж
     */
    protected function getSalesAnalytics($business, $dateRange)
    {
        $sales = Purchase::whereHas('certificates', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })
            ->whereBetween('paid_at', $dateRange)
            ->select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total'),
                DB::raw('SUM(commission) as commission')
            )
            ->groupBy('date')
            ->get();

        // Группировка по сегментам
        $bySegment = Certificate::where('business_id', $business->id)
            ->whereBetween('created_at', $dateRange)
            ->with('segment')
            ->select('segment_id', DB::raw('COUNT(*) as count'))
            ->groupBy('segment_id')
            ->get()
            ->map(function ($item) {
                return [
                    'segment' => $item->segment?->name ?? 'Без сегмента',
                    'count' => $item->count,
                ];
            });

        return [
            'total' => [
                'count' => $sales->sum('count'),
                'amount' => $sales->sum('total'),
                'commission' => $sales->sum('commission'),
            ],
            'by_segment' => $bySegment,
            'trend' => $this->calculateTrend($sales),
            'chart_data' => $sales,
        ];
    }

    /**
     * Аналитика гашений
     */
    protected function getRedemptionAnalytics($business, $dateRange)
    {
        $redemptions = CertificateRedemption::where('business_id', $business->id)
            ->whereBetween('redeemed_at', $dateRange)
            ->select(
                DB::raw('DATE(redeemed_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('date')
            ->get();

        return [
            'today' => CertificateRedemption::where('business_id', $business->id)
                ->whereDate('redeemed_at', today())
                ->count(),
            'total' => [
                'count' => $redemptions->sum('count'),
                'amount' => $redemptions->sum('total'),
            ],
            'chart_data' => $redemptions,
        ];
    }

    /**
     * Статистика SmartShare
     */
    protected function getSmartShareAnalytics($business, $dateRange)
    {
        $totalCertificates = Certificate::where('business_id', $business->id)
            ->whereBetween('created_at', $dateRange)
            ->count();

        $splitCertificates = Certificate::where('business_id', $business->id)
            ->whereBetween('created_at', $dateRange)
            ->whereHas('splits')
            ->count();

        $splits = CertificateSplit::whereHas('parent', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })
            ->whereBetween('created_at', $dateRange)
            ->get();

        $averageCheck = Purchase::whereHas('certificates', function ($q) use ($business) {
            $q->where('business_id', $business->id);
        })
            ->whereBetween('paid_at', $dateRange)
            ->avg('amount');

        $splitAverageCheck = Purchase::whereHas('certificates', function ($q) use ($business) {
            $q->where('business_id', $business->id)
                ->whereHas('splits');
        })
            ->whereBetween('paid_at', $dateRange)
            ->avg('amount');

        return [
            'total_certificates' => $totalCertificates,
            'split_certificates' => $splitCertificates,
            'split_percentage' => $totalCertificates > 0
                ? round(($splitCertificates / $totalCertificates) * 100, 2)
                : 0,
            'total_splits' => $splits->count(),
            'average_check' => $averageCheck,
            'split_average_check' => $splitAverageCheck,
            'check_increase' => $averageCheck > 0
                ? round((($splitAverageCheck - $averageCheck) / $averageCheck) * 100, 2)
                : 0,
        ];
    }

    /**
     * Топ локаций по гашениям
     */
    protected function getTopLocations($business, $dateRange)
    {
        return CertificateRedemption::where('business_id', $business->id)
            ->whereBetween('redeemed_at', $dateRange)
            ->whereNotNull('location_id')
            ->with('location')
            ->select('location_id', DB::raw('COUNT(*) as redemption_count'))
            ->groupBy('location_id')
            ->orderBy('redemption_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'address' => $item->location?->address,
                    'redemptions' => $item->redemption_count,
                    'percentage' => '67%', // TODO: Рассчитать процент от общего числа
                ];
            });
    }

    /**
     * Сводная статистика
     */
    protected function getSummaryStats($business, $dateRange)
    {
        return [
            'total_sales' => Purchase::whereHas('certificates', function ($q) use ($business) {
                $q->where('business_id', $business->id);
            })->whereBetween('paid_at', $dateRange)->sum('amount'),

            'total_redemptions' => CertificateRedemption::where('business_id', $business->id)
                ->whereBetween('redeemed_at', $dateRange)
                ->sum('amount'),

            'active_certificates' => Certificate::where('business_id', $business->id)
                ->where('status', 'active')
                ->count(),

            'total_balance' => Certificate::where('business_id', $business->id)
                ->where('status', 'active')
                ->sum('balance'),
        ];
    }

    /**
     * Получить диапазон дат для аналитики
     */
    protected function getDateRange(string $period): array
    {
        $end = now();

        $start = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subQuarter(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        return [$start, $end];
    }

    /**
     * Рассчитать тренд
     */
    protected function calculateTrend($data): array
    {
        // TODO: Рассчитать процент изменения по сравнению с предыдущим периодом
        return [
            'direction' => 'up',
            'percentage' => 23,
        ];
    }
}
