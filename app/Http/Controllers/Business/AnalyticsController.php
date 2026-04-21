<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\GiftCertificate;
use App\Models\GiftCertificateRedemption;
use App\Models\Order;
use App\Services\Notification\PDFGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    private function requireStartOrPro(Request $request): void
    {
        $plan = $request->user()->organization?->planKey() ?? 'free';
        if (! in_array($plan, ['start', 'pro'], true)) {
            abort(403, 'Функция доступна только на тарифах Start и Pro.');
        }
    }

    public function dashboard(Request $request): Response
    {
        return Inertia::render('Business/Analytics/Dashboard');
    }

    public function data(Request $request)
    {
        $organizationId = $request->user()->organization_id;
        $group = $request->string('group', 'day')->toString(); // day|week|month

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'group' => ['nullable', 'in:day,week,month'],
        ]);

        $to = $request->date('to') ?? now();
        $from = $request->date('from') ?? now()->subMonth();

        $paidOrders = Order::query()
            ->where('organization_id', $organizationId)
            ->where('status', Order::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to]);

        $salesCount = (clone $paidOrders)->count();
        $revenue = (clone $paidOrders)->sum('total_amount');
        $feePercent = $request->user()->organization?->transactionFeePercent() ?? 3.0;
        $retainedCommission = round(((float) $revenue) * ($feePercent / 100), 2);

        $redeems = GiftCertificateRedemption::query()
            ->where('organization_id', $organizationId)
            ->whereBetween('redeemed_at', [$from, $to]);

        $usedCount = (clone $redeems)->count();
        $usedAmount = (clone $redeems)->sum('amount');

        $salesSeries = $this->series((clone $paidOrders), 'paid_at', 'total_amount', $group);
        $redeemSeries = $this->series((clone $redeems), 'redeemed_at', 'amount', $group);

        $nominalPopularity = GiftCertificate::query()
            ->where('organization_id', $organizationId)
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [$from, $to])
            ->select('amount', DB::raw('COUNT(*) as cnt'))
            ->groupBy('amount')
            ->orderByDesc('cnt')
            ->limit(12)
            ->get()
            ->map(fn ($r) => ['amount' => (float) $r->amount, 'count' => (int) $r->cnt]);

        return response()->json([
            'data' => [
                'summary' => [
                    'sales_count' => $salesCount,
                    'revenue' => (float) $revenue,
                    'used_certificates' => $usedCount,
                    'retained_commission' => (float) $retainedCommission,
                    'used_amount' => (float) $usedAmount,
                    'fee_percent' => (float) $feePercent,
                ],
                'series' => [
                    'sales' => $salesSeries,
                    'redeems' => $redeemSeries,
                ],
                'nominal_popularity' => $nominalPopularity,
                'range' => [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'group' => $group,
                ],
            ],
        ]);
    }

    public function exportCsv(Request $request)
    {
        $this->requireStartOrPro($request);
        $organizationId = $request->user()->organization_id;

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $to = $request->date('to') ?? now();
        $from = $request->date('from') ?? now()->subMonth();

        $rows = Order::query()
            ->where('organization_id', $organizationId)
            ->where('status', Order::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to])
            ->orderBy('paid_at')
            ->get(['number', 'paid_at', 'total_amount', 'recipient_email', 'recipient_name']);

        $filename = 'analytics_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        $out = fopen('php://temp', 'w+');
        fputcsv($out, ['order_number', 'paid_at', 'total_amount', 'recipient_email', 'recipient_name']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r->number,
                optional($r->paid_at)->toDateTimeString(),
                (string) $r->total_amount,
                $r->recipient_email,
                $r->recipient_name,
            ]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPdf(Request $request, PDFGenerator $pdf)
    {
        $this->requireStartOrPro($request);
        $organizationId = $request->user()->organization_id;

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $to = $request->date('to') ?? now();
        $from = $request->date('from') ?? now()->subMonth();

        $orders = Order::query()
            ->where('organization_id', $organizationId)
            ->where('status', Order::STATUS_PAID)
            ->whereBetween('paid_at', [$from, $to])
            ->orderBy('paid_at')
            ->get();

        $revenue = $orders->sum('total_amount');
        $feePercent = $request->user()->organization?->transactionFeePercent() ?? 3.0;
        $commission = round(((float) $revenue) * ($feePercent / 100), 2);

        $html = view('reports.analytics', [
            'organization' => $request->user()->organization,
            'from' => $from,
            'to' => $to,
            'orders' => $orders,
            'revenue' => $revenue,
            'commission' => $commission,
            'feePercent' => $feePercent,
        ])->render();

        // Используем Dompdf напрямую (через наш сервис уже подтянуты зависимости)
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'analytics_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportBuyersCsv(Request $request)
    {
        $this->requireStartOrPro($request);

        $organizationId = $request->user()->organization_id;

        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $to = $request->date('to') ?? now();
        $from = $request->date('from') ?? now()->subMonth();

        $emails = \App\Models\User::query()
            ->join('orders', 'orders.user_id', '=', 'users.id')
            ->where('orders.organization_id', $organizationId)
            ->where('orders.status', \App\Models\Order::STATUS_PAID)
            ->whereBetween('orders.paid_at', [$from, $to])
            ->whereNotNull('users.email')
            ->distinct()
            ->orderBy('users.email')
            ->pluck('users.email');

        $filename = 'buyers_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';

        $out = fopen('php://temp', 'w+');
        fputcsv($out, ['email']);
        foreach ($emails as $email) {
            fputcsv($out, [$email]);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function series($baseQuery, string $dateColumn, string $sumColumn, string $group): array
    {
        $driver = DB::getDriverName();

        $bucketSql = match ($group) {
            'week' => match ($driver) {
                'pgsql' => "to_char({$dateColumn}, 'IYYY-IW')",
                'mysql', 'mariadb' => "DATE_FORMAT({$dateColumn}, '%x-%v')",
                default => "strftime('%Y-%W', {$dateColumn})",
            },
            'month' => match ($driver) {
                'pgsql' => "to_char({$dateColumn}, 'YYYY-MM')",
                'mysql', 'mariadb' => "DATE_FORMAT({$dateColumn}, '%Y-%m')",
                default => "strftime('%Y-%m', {$dateColumn})",
            },
            default => match ($driver) {
                'pgsql' => "to_char({$dateColumn}, 'YYYY-MM-DD')",
                'mysql', 'mariadb' => "DATE({$dateColumn})",
                default => "date({$dateColumn})",
            },
        };

        // Получаем результаты и преобразуем их в массив с явным приведением типов
        $results = $baseQuery
            ->selectRaw("{$bucketSql} as bucket")
            ->selectRaw('COUNT(*) as count')
            ->selectRaw("SUM({$sumColumn}) as total")
            ->groupByRaw($bucketSql)
            ->orderBy('bucket')
            ->get();

        // Преобразуем каждый результат в обычный массив
        return $results->map(function ($r) {
            return [
                'bucket' => (string) $r->bucket,  // Явное преобразование в строку
                'count' => (int) $r->count,
                'total' => (float) $r->total,
            ];
        })->values()->all(); // values() для переиндексации
    }
}

