<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявка одобрена - GiftHub</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #ec4899, #fbbf24);
            padding: 40px 30px;
            text-align: center;
        }
        .logo {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: white;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .message {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .business-details {
            background-color: #f3f4f6;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
        }
        .business-details h3 {
            color: #1f2937;
            font-size: 16px;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        .detail-row {
            display: flex;
            margin-bottom: 12px;
        }
        .detail-label {
            width: 120px;
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }
        .detail-value {
            flex: 1;
            color: #1f2937;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #ec4899, #fbbf24);
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 40px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .footer {
            text-align: center;
            padding: 30px;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
        }
        .footer a {
            color: #ec4899;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            .detail-row {
                flex-direction: column;
            }
            .detail-label {
                width: auto;
                margin-bottom: 4px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header">
            <div class="logo">🎁</div>
            <h1 class="title">Заявка одобрена!</h1>
        </div>

        <div class="content">
            <div class="greeting">
                Здравствуйте, {{ $registerRequest->contact_person ?? $registerRequest->name ?? 'уважаемый пользователь' }}!
            </div>

            <div class="message">
                <p>✅ Ваша заявка на регистрацию бизнеса на платформе <strong>GiftHub</strong> была <strong>одобрена</strong>.</p>
                <p>Теперь вы можете войти в свой аккаунт и начать создавать подарочные сертификаты, управлять магазинами и отслеживать продажи.</p>
            </div>

            <div class="business-details">
                <h3>📋 Данные вашего бизнеса</h3>
                <div class="detail-row">
                    <div class="detail-label">УНП:</div>
                    <div class="detail-value">{{ $registerRequest->unp ?? '—' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Наименование:</div>
                    <div class="detail-value">{{ $registerRequest->name ?? '—' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Форма собственности:</div>
                    <div class="detail-value">{{ $registerRequest->form_of_own ?? '—' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Контактное лицо:</div>
                    <div class="detail-value">{{ $registerRequest->contact ?? '—' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">{{ $registerRequest->email ?? '—' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Телефон:</div>
                    <div class="detail-value">{{ $registerRequest->phone ?? '—' }}</div>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/login" class="button">
                    Войти в аккаунт →
                </a>
            </div>

            <div class="message" style="font-size: 14px; margin-top: 20px;">
                <p>💡 <strong>Что дальше?</strong></p>
                <ul style="color: #4b5563; padding-left: 20px;">
                    <li>Заполните информацию о ваших магазинах (адреса, часы работы)</li>
                    <li>Создайте подарочные сертификаты с разными номиналами</li>
                    <li>Начните продавать сертификаты через наш маркетплейс</li>
                </ul>
            </div>

            <div class="message" style="background-color: #fef3c7; padding: 15px; border-radius: 8px; margin-top: 20px;">
                <p style="margin: 0; color: #92400e;">
                    ⚠️ Если у вас возникли вопросы или вы не подавали заявку, пожалуйста, свяжитесь с нами по адресу support@gifthub.by
                </p>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} GiftHub. Все права защищены.</p>
            <p>
                <a href="{{ config('app.url') }}">gifthub.by</a> |
                <a href="mailto:support@gifthub.by">support@gifthub.by</a>
            </p>
            <p style="margin-top: 10px;">
                Это письмо было отправлено автоматически, пожалуйста, не отвечайте на него.
            </p>
        </div>
    </div>
</div>
</body>
</html>
