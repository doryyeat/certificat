<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Списание по сертификату</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111827; background:#F9FAFB; padding: 24px;">
<div style="max-width: 640px; margin: 0 auto; background:#ffffff; border:1px solid #E5E7EB; border-radius:16px; overflow:hidden;">
    <div style="background: linear-gradient(135deg,#4F46E5,#7C3AED); padding: 22px 20px; color:#fff;">
        <div style="font-size:18px; font-weight:700;">Обновление баланса сертификата</div>
        <div style="font-size:12px; opacity:.9; margin-top:6px;">{{ $organizationName }}</div>
    </div>
    <div style="padding: 18px 20px;">
        <p style="margin:0 0 12px 0;">Здравствуйте, <strong>{{ $recipientName }}</strong>!</p>
        <p style="margin:0 0 12px 0; color:#374151;">
            По вашему сертификату выполнено списание.
        </p>
        <div style="border:1px solid #E5E7EB; border-radius:12px; padding:12px 14px; margin: 14px 0;">
            <div style="font-size:12px; color:#6B7280;">Код сертификата</div>
            <div style="font-weight:700; font-family: monospace;">{{ $certificateCode }}</div>
            <div style="height:10px;"></div>
            <div style="font-size:12px; color:#6B7280;">Списано</div>
            <div style="font-weight:700;">{{ number_format($amountRedeemed, 2, '.', ' ') }} {{ $currency }}</div>
            <div style="height:10px;"></div>
            <div style="font-size:12px; color:#6B7280;">Остаток</div>
            <div style="font-weight:700;">{{ number_format($balanceLeft, 2, '.', ' ') }} {{ $currency }}</div>
        </div>
    </div>
</div>
</body>
</html>

