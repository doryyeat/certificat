<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Подарочные сертификаты</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111827; background:#F9FAFB; padding: 24px;">
<div style="max-width: 640px; margin: 0 auto; background:#ffffff; border:1px solid #E5E7EB; border-radius:16px; overflow:hidden;">
    <div style="background: linear-gradient(135deg,#4F46E5,#7C3AED); padding: 22px 20px; color:#fff;">
        <div style="font-size:18px; font-weight:700;">Ваш подарочный сертификат</div>
        <div style="font-size:12px; opacity:.9; margin-top:6px;">Заказ №{{ $orderNumber }}</div>
    </div>
    <div style="padding: 18px 20px;">
        <p style="margin:0 0 12px 0;">Здравствуйте, <strong>{{ $recipientName }}</strong>!</p>
        @if($messageText)
            <div style="background:#FFFBEB; border:1px solid #FDE68A; border-left:4px solid #F59E0B; padding:12px; border-radius:12px; margin: 12px 0 16px 0;">
                <div style="font-weight:700; margin-bottom:6px;">Сообщение</div>
                <div style="white-space: pre-line;">{{ $messageText }}</div>
            </div>
        @endif

        <p style="margin:0 0 12px 0; color:#374151;">
            Мы прикрепили PDF‑файлы сертификатов к письму. В каждом PDF справа расположен QR‑код для предъявления в точке продаж.
        </p>

        <div style="margin-top: 14px;">
            @foreach($certificates as $cert)
                <div style="border:1px solid #E5E7EB; border-radius:12px; padding:12px 14px; margin-bottom:10px;">
                    <div style="font-weight:700;">{{ $cert->title }}</div>
                    <div style="font-size:12px; color:#6B7280; margin-top:4px;">
                        Код: <strong>{{ $cert->code }}</strong> • Сумма: <strong>{{ number_format((float)$cert->amount, 2, '.', ' ') }} {{ $cert->currency }}</strong>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div style="padding: 14px 20px; border-top:1px solid #E5E7EB; font-size:12px; color:#6B7280;">
        Это письмо сформировано автоматически.
    </div>
</div>
</body>
</html>

