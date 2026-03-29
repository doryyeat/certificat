<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Certificate;
use App\Models\Purchase;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Получить аналитику платформы (для админа)
     *
     * GET /api/admin/dashboard
     */
    public function index(Request $request)
    {
        $period = $request->period ?? 'month';
        $dateRange = $this->getDateRange($period);

        // MRR (Monthly Recurring Revenue)
        $mrr = $this->calculateMRR($dateRange);

        // ARPU (Average Revenue Per User)
        $arpu = $this->calculateARPU($dateRange);

        // Churn rate
        $churn = $this->calculateChurnRate($dateRange);

        // Новые бизнесы
        $newBusinesses = $this->getNewBusinesses($dateRange);

        return response()->json([
            'data' => [
                'mrr' => $mrr,
                'arpu' => $arpu,
                'churn' => $churn,
                'new_businesses' => $newBusinesses,
                'subscription_distribution' => $this->getSubscriptionDistribution(),
                'revenue_chart' => $this->getRevenueChart($dateRange),
                'top_businesses' => $this->getTopBusinesses(),
            ]
        ]);
    }

    /**
     * Рассчитать MRR
     */
    protected function calculateMRR($dateRange): array
    {
        $currentMRR = Subscription::where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->sum('price');

        $previousMRR = Subscription::where('status', 'active')
            ->where('starts_at', '<=', $dateRange[0])
            ->where(function ($q) use ($dateRange) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $dateRange[0]);
            })
            ->sum('price');

        $change = $previousMRR > 0
            ? round((($currentMRR - $previousMRR) / $previousMRR) * 100, 2)
            : 0;

        return [
            'current' => $currentMRR,
            'previous' => $previousMRR,
            'change' => $change,
            'change_direction' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Рассчитать ARPU
     */
    protected function calculateARPU($dateRange): array
    {
        $totalRevenue = Purchase::whereBetween('paid_at', $dateRange)
            ->sum('amount');

        $activeBusinesses = Business::whereHas('subscriptions', function ($q) {
            $q->where('status', 'active');
        })
            ->count();

        $currentARPU = $activeBusinesses > 0
            ? round($totalRevenue / $activeBusinesses, 2)
            : 0;

        return [
            'current' => $currentARPU,
            'total_revenue' => $totalRevenue,
            'active_businesses' => $activeBusinesses,
        ];
    }

    /**
     * Рассчитать Churn Rate
     */
    protected function calculateChurnRate($dateRange): array
    {
        $startCount = Business::where('created_at', '<=', $dateRange[0])->count();

        $canceledCount = Subscription::where('status', 'canceled')
            ->whereBetween('canceled_at', $dateRange)
            ->distinct('business_id')
            ->count('business_id');

        $churnRate = $startCount > 0
            ? round(($canceledCount / $startCount) * 100, 2)
            : 0;

        return [
            'rate' => $churnRate,
            'canceled' => $canceledCount,
            'total_start' => $startCount,
        ];
    }

    /**
     * Получить новые бизнесы
     */
    protected function getNewBusinesses($dateRange): array
    {
        $total = Business::whereBetween('created_at', $dateRange)->count();

        $byPlan = Business::whereBetween('created_at', $dateRange)
            ->select('subscription', DB::raw('COUNT(*) as count'))
            ->groupBy('subscription')
            ->get()
            ->pluck('count', 'subscription')
            ->toArray();

        return [
            'total' => $total,
            'by_plan' => $byPlan,
            'trend' => $this->calculateBusinessTrend($dateRange),
        ];
    }

    /**
     * Получить распределение по подпискам
     */
    protected function getSubscriptionDistribution(): array
    {
        return Business::select('subscription', DB::raw('COUNT(*) as count'))
            ->groupBy('subscription')
            ->get()
            ->map(function ($item) {
                return [
                    'plan' => $item->subscription,
                    'count' => $item->count,
                ];
            });
    }

    /**
     * Получить данные для графика выручки
     */
    protected function getRevenueChart($dateRange): array
    {
        return Purchase::whereBetween('paid_at', $dateRange)
            ->select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Получить топ бизнесов по выручке
     */
    protected function getTopBusinesses(): array
    {
        return Business::withCount(['certificates as total_sales' => function ($q) {
            $q->select(DB::raw('COALESCE(SUM(nominal), 0)'));
        }])
            ->orderBy('total_sales', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($business) {
                return [
                    'id' => $business->id,
                    'name' => $business->name,
                    'subscription' => $business->subscription,
                    'total_sales' => $business->total_sales,
                    'verified' => $business->is_verified,
                ];
            });
    }

    /**
     * Рассчитать тренд новых бизнесов
     */
    protected function calculateBusinessTrend($dateRange): array
    {
        $currentPeriod = Business::whereBetween('created_at', $dateRange)->count();

        $previousStart = clone $dateRange[0];
        $previousEnd = clone $dateRange[1];
        $interval = $dateRange[0]->diff($dateRange[1]);

        $previousPeriod = [
            (clone $dateRange[0])->sub($interval),
            (clone $dateRange[1])->sub($interval),
        ];

        $previousCount = Business::whereBetween('created_at', $previousPeriod)->count();

        $change = $previousCount > 0
            ? round((($currentPeriod - $previousCount) / $previousCount) * 100, 2)
            : 0;

        return [
            'current' => $currentPeriod,
            'previous' => $previousCount,
            'change' => $change,
            'direction' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Получить диапазон дат
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
}
