<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Отчёт по продажам</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        .muted { color: #6B7280; }
        .card { border: 1px solid #E5E7EB; border-radius: 12px; padding: 12px; margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border-bottom: 1px solid #E5E7EB; padding: 8px 6px; text-align: left; }
        th { background: #F9FAFB; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Отчёт по продажам</h1>
    <div class="muted">
        Организация: <strong>{{ $organization?->name ?? '—' }}</strong><br>
        Период: {{ $from->format('d.m.Y') }} — {{ $to->format('d.m.Y') }}
    </div>

    <div class="card">
        <strong>Итого</strong><br>
        Выручка: {{ number_format((float)$revenue, 2, '.', ' ') }}<br>
        Комиссия ({{ $feePercent }}%): {{ number_format((float)$commission, 2, '.', ' ') }}<br>
        Количество заказов: {{ $orders->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Заказ</th>
                <th>Оплачен</th>
                <th class="right">Сумма</th>
                <th>Получатель</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $o)
                <tr>
                    <td>{{ $o->number }}</td>
                    <td>{{ optional($o->paid_at)->format('d.m.Y H:i') }}</td>
                    <td class="right">{{ number_format((float)$o->total_amount, 2, '.', ' ') }}</td>
                    <td>{{ $o->recipient_email ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

