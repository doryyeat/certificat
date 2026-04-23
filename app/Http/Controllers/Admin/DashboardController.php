<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Order;
use App\Models\PurchasedCertificate;
use App\Models\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->getStats($request),
            'charts' => $this->getChartData($request),
            'topOrganizations' => $this->getTopOrganizations($request),
            'recentActivities' => $this->getRecentActivities(),
        ]);
    }

    public function data(Request $request)
    {
        return response()->json([
            'stats' => $this->getStats($request),
            'charts' => $this->getChartData($request),
            'topOrganizations' => $this->getTopOrganizations($request),
        ]);
    }

    private function getStats(Request $request): array
    {
        $organizationId = $request->input('organization_id');

        $organizationsQuery = Organization::query();
        if ($organizationId) {
            $organizationsQuery->where('id', $organizationId);
        }

        // Статистика по организациям
        $totalOrganizations = (clone $organizationsQuery)->count();

        $organizationsByPlan = (clone $organizationsQuery)
            ->select('plan_name', DB::raw('COUNT(*) as count'))
            ->groupBy('plan_name')
            ->get()
            ->map(fn($item) => [
                'name' => $item->plan_name ?? 'free',
                'count' => $item->count,
                'color' => $this->getPlanColor($item->plan_name ?? 'free'),
            ])
            ->values();

        // Активные подписки (срок не истек)
        $activeSubscriptions = (clone $organizationsQuery)
            ->where('subscription_active_until', '>', now())
            ->count();

        // Просроченные подписки
        $expiredSubscriptions = (clone $organizationsQuery)
            ->where('subscription_active_until', '<', now())
            ->whereNotNull('subscription_active_until')
            ->count();

        // Статистика по продажам сертификатов
        $certificatesQuery = PurchasedCertificate::query();
        if ($organizationId) {
            $certificatesQuery->where('organization_id', $organizationId);
        }

        $totalSoldCertificates = (clone $certificatesQuery)->count();
        $totalRevenue = (clone $certificatesQuery)->sum('amount');
        $totalBalance = (clone $certificatesQuery)->sum('balance');
        $usedAmount = $totalRevenue - $totalBalance;

        // Статистика по заказам
        $ordersQuery = Order::query()->where('status', Order::STATUS_PAID);
        if ($organizationId) {
            $ordersQuery->where('organization_id', $organizationId);
        }

        $totalOrders = (clone $ordersQuery)->count();
        $totalOrderRevenue = (clone $ordersQuery)->sum('total_amount');

        // Комиссия (3% от выручки)
        $totalCommission = $totalOrderRevenue * 0.03;

        // Заявки
        $pendingRequests = RegisterRequest::where('status', RegisterRequest::STATUS_PENDING)->count();
        $totalBusinessUsers = User::role('business')->count();

        return [
            'organizations' => [
                'total' => $totalOrganizations,
                'by_plan' => $organizationsByPlan,
                'active_subscriptions' => $activeSubscriptions,
                'expired_subscriptions' => $expiredSubscriptions,
            ],
            'certificates' => [
                'total_sold' => $totalSoldCertificates,
                'total_revenue' => round($totalRevenue, 2),
                'total_balance' => round($totalBalance, 2),
                'used_amount' => round($usedAmount, 2),
                'utilization_rate' => $totalRevenue > 0
                    ? round(($usedAmount / $totalRevenue) * 100, 2)
                    : 0,
            ],
            'orders' => [
                'total_count' => $totalOrders,
                'total_revenue' => round($totalOrderRevenue, 2),
                'total_commission' => round($totalCommission, 2),
                'average_order_value' => $totalOrders > 0
                    ? round($totalOrderRevenue / $totalOrders, 2)
                    : 0,
            ],
            'users' => [
                'business_users' => $totalBusinessUsers,
                'pending_requests' => $pendingRequests,
            ],
        ];
    }

    private function getChartData(Request $request): array
    {
        $organizationId = $request->input('organization_id');
        $period = $request->input('period', 'month'); // week, month, year

        $startDate = match ($period) {
            'week' => now()->subDays(7),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        // Продажи по дням
        $salesQuery = PurchasedCertificate::query()
            ->select(
                DB::raw('DATE(sold_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as revenue')
            )
            ->where('sold_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date');

        if ($organizationId) {
            $salesQuery->where('organization_id', $organizationId);
        }

        $salesData = $salesQuery->get();

        // Регистрации организаций по дням
        $registrationsQuery = Organization::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date');

        $registrationsData = $registrationsQuery->get();

        // Продажи по тарифам
        $salesByPlan = PurchasedCertificate::query()
            ->join('organizations', 'purchased_certificates.organization_id', '=', 'organizations.id')
            ->select(
                'organizations.plan_name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(purchased_certificates.amount) as revenue')
            )
            ->where('sold_at', '>=', $startDate)
            ->groupBy('organizations.plan_name')
            ->get()
            ->map(fn($item) => [
                'name' => $item->plan_name ?? 'free',
                'count' => $item->count,
                'revenue' => round($item->revenue, 2),
                'color' => $this->getPlanColor($item->plan_name ?? 'free'),
            ]);

        return [
            'sales' => [
                'labels' => $salesData->pluck('date'),
                'counts' => $salesData->pluck('count'),
                'revenues' => $salesData->pluck('revenue'),
            ],
            'registrations' => [
                'labels' => $registrationsData->pluck('date'),
                'counts' => $registrationsData->pluck('count'),
            ],
            'sales_by_plan' => $salesByPlan,
            'period' => $period,
        ];
    }

    private function getTopOrganizations(Request $request): array
    {
        $period = $request->input('period', 'month');
        $limit = $request->input('limit', 10);

        $startDate = match ($period) {
            'week' => now()->subDays(7),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            'all' => null,
            default => now()->subMonth(),
        };

        $query = PurchasedCertificate::query()
            ->join('organizations', 'purchased_certificates.organization_id', '=', 'organizations.id')
            ->select(
                'organizations.id',
                'organizations.name',
                'organizations.plan_name',
                DB::raw('COUNT(*) as certificates_sold'),
                DB::raw('SUM(purchased_certificates.amount) as total_revenue'),
                DB::raw('SUM(purchased_certificates.balance) as remaining_balance'),
                DB::raw('MAX(purchased_certificates.sold_at) as last_sale')
            )
            ->groupBy('organizations.id', 'organizations.name', 'organizations.plan_name')
            ->orderByDesc('total_revenue')
            ->limit($limit);

        if ($startDate) {
            $query->where('purchased_certificates.sold_at', '>=', $startDate);
        }

        $results = $query->get();

        // Расчет использованной суммы
        foreach ($results as $org) {
            $org->used_amount = $org->total_revenue - $org->remaining_balance;
            $org->utilization_rate = $org->total_revenue > 0
                ? round(($org->used_amount / $org->total_revenue) * 100, 2)
                : 0;
            $org->total_revenue = round($org->total_revenue, 2);
            $org->remaining_balance = round($org->remaining_balance, 2);
        }

        return $results->toArray();
    }

    private function getRecentActivities(): array
    {
        // Последние зарегистрированные организации
        $recentOrganizations = Organization::query()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'name', 'plan_name', 'created_at'])
            ->map(fn($org) => [
                'type' => 'organization',
                'id' => $org->id,
                'name' => $org->name,
                'plan' => $org->plan_name ?? 'free',
                'created_at' => $org->created_at,
                'message' => "Новая организация: {$org->name}",
            ]);

        // Последние одобренные заявки
        $recentRequests = RegisterRequest::query()
            ->where('status', RegisterRequest::STATUS_APPROVED)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get()
            ->map(fn($req) => [
                'type' => 'request',
                'id' => $req->id,
                'name' => $req->company_name,
                'created_at' => $req->updated_at,
                'message' => "Одобрена заявка от {$req->company_name}",
            ]);

        // Последние продажи
        $recentSales = PurchasedCertificate::query()
            ->with('organization')
            ->whereNotNull('sold_at')
            ->orderByDesc('sold_at')
            ->limit(5)
            ->get()
            ->map(fn($sale) => [
                'type' => 'sale',
                'id' => $sale->id,
                'name' => $sale->organization?->name ?? 'Неизвестно',
                'amount' => $sale->amount,
                'code' => $sale->code,
                'sold_at' => $sale->sold_at,
                'message' => "Продан сертификат на {$sale->amount} BYN",
            ]);

        // Объединяем и сортируем по дате
        $activities = $recentOrganizations
            ->concat($recentRequests)
            ->concat($recentSales)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        return $activities->toArray();
    }

    private function getPlanColor(string $plan): string
    {
        return match ($plan) {
            'pro' => '#10B981', // green
            'start' => '#F59E0B', // amber
            'free' => '#6B7280', // gray
            default => '#3B82F6', // blue
        };
    }
}
