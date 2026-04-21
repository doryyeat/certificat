<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Подарочный сертификат</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; margin: 0; padding: 0; }
        .page { padding: 28px; }
        .card {
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #E5E7EB;
        }
        .header {
            @if(($branding['plan'] ?? 'free') === 'free')
            background: linear-gradient(135deg, #6B7280 0%, #9CA3AF 100%);
            @else
            background: linear-gradient(135deg, {{ ($branding['colors'][0] ?? '#4F46E5') }} 0%, {{ ($branding['colors'][1] ?? ($branding['colors'][0] ?? '#7C3AED')) }} 100%);
            @endif
            color: #fff;
            padding: 22px 26px;
        }
        .header-title { font-size: 20px; font-weight: 700; margin: 0; }
        .header-subtitle { font-size: 12px; opacity: .9; margin-top: 6px; }
        .body { padding: 26px; display: table; width: 100%; }
        .left, .right { display: table-cell; vertical-align: top; }
        .left { width: 68%; padding-right: 18px; }
        .right { width: 32%; padding-left: 18px; border-left: 1px solid #E5E7EB; }
        .amount { font-size: 44px; font-weight: 800; color: #111827; margin: 0 0 10px 0; }
        .meta { background: #F9FAFB; border-radius: 16px; padding: 14px 16px; }
        .row { display: table; width: 100%; padding: 8px 0; border-bottom: 1px solid #E5E7EB; }
        .row:last-child { border-bottom: 0; }
        .k, .v { display: table-cell; font-size: 12px; }
        .k { color: #6B7280; width: 45%; }
        .v { color: #111827; font-weight: 700; text-align: right; }
        .note { margin-top: 14px; font-size: 11px; color: #374151; line-height: 1.45; }
        .qr-wrap { text-align: center; }
        .qr-img { width: 220px; height: 220px; border-radius: 16px; border: 1px solid #E5E7EB; padding: 10px; background: #fff; }
        .qr-caption { margin-top: 10px; font-size: 11px; color: #6B7280; }
        .footer { padding: 16px 26px; border-top: 1px solid #E5E7EB; font-size: 10px; color: #6B7280; }
        .brand { font-weight: 700; color: #111827; }
        .bg {
            @if(!empty($branding['backgroundBase64']))
            background-image: url('{{ $branding['backgroundBase64'] }}');
            background-size: cover;
            background-position: center;
            @endif
        }
        .logo { height: 34px; vertical-align: middle; }
        .header-row { display: table; width: 100%; }
        .header-left, .header-right { display: table-cell; vertical-align: middle; }
        .header-right { text-align: right; }
    </style>
</head>
<body>
@php
    $branding = $branding ?? ['plan' => 'free', 'colors' => [], 'logoBase64' => null, 'backgroundBase64' => null];
@endphp
<div class="page">
    <div class="card bg">
        <div class="header">
            <div class="header-row">
                <div class="header-left">
                    <div class="header-title">{{ optional($certificate->organization)->name ?? 'Организация' }}</div>
                    <div class="header-subtitle">
                        {{ $certificate->title ?? 'Подарочный сертификат' }}
                        @if(optional($certificate->store)->address)
                            • {{ $certificate->store->address }}
                        @endif
                    </div>
                </div>
                <div class="header-right">
                    @if(!empty($branding['logoBase64']))
                        <img class="logo" src="{{ $branding['logoBase64'] }}" alt="Logo">
                    @endif
                </div>
            </div>
        </div>

        <div class="body">
            <div class="left">
                <p class="amount">
                    {{ number_format((float)$certificate->amount, 2, '.', ' ') }} {{ $certificate->currency }}
                </p>

                <div class="meta">
                    <div class="row">
                        <div class="k">Код сертификата</div>
                        <div class="v">{{ $certificate->code }}</div>
                    </div>
                    <div class="row">
                        <div class="k">Остаток</div>
                        <div class="v">{{ number_format((float)$certificate->balance, 2, '.', ' ') }} {{ $certificate->currency }}</div>
                    </div>
                    <div class="row">
                        <div class="k">Действителен до</div>
                        <div class="v">{{ optional($certificate->expires_at)->format('d.m.Y') ?? '—' }}</div>
                    </div>
                    <div class="row">
                        <div class="k">Получатель</div>
                        <div class="v">{{ $certificate->recipient_name ?: ($certificate->recipient_email ?: '—') }}</div>
                    </div>
                    <div class="row">
                        <div class="k">Категория</div>
                        <div class="v">{{ $certificate->category ?? '—' }}</div>
                    </div>
                    <div class="row">
                        <div class="k">Точка продаж</div>
                        <div class="v">{{ optional($certificate->store)->address ?? optional($certificate->store)->name ?? '—' }}</div>
                    </div>
                </div>

                @if($certificate->notes)
                    <div class="note">
                        <span class="brand">Сообщение:</span> {{ $certificate->notes }}
                    </div>
                @endif

                @if($certificate->terms_of_use)
                    <div class="note">
                        <span class="brand">Условия использования:</span> {{ $certificate->terms_of_use }}
                    </div>
                @endif
            </div>

            <div class="right">
                <div class="qr-wrap">
                    <img class="qr-img" src="data:image/png;base64,{{ $qrPngBase64 }}" alt="QR" />
                    <div class="qr-caption">
                        Предъявите QR‑код продавцу для списания.
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <span class="brand">Сертификат</span> действует до указанной даты. При частичном списании остаток сохраняется до истечения срока.
        </div>
    </div>
</div>
</body>
</html>

