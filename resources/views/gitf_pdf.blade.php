<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        background-color: #f5f5f5;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
    }

    .certificate-card {
        max-width: 600px;
        width: 100%;
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* Header с градиентом */
    .certificate-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 32px 24px;
        text-align: center;
    }

    .exchange-badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        display: inline-block;
        padding: 8px 16px;
        border-radius: 100px;
        color: white;
        font-size: 14px;
        margin-bottom: 16px;
    }

    .price {
        font-size: 48px;
        font-weight: bold;
        color: white;
        margin-bottom: 8px;
    }

    .price small {
        font-size: 24px;
        opacity: 0.9;
    }

    .card-title {
        color: white;
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .card-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
    }

    /* Основной контент */
    .certificate-content {
        padding: 32px 24px;
    }

    /* Информационная сетка */
    .info-grid {
        background: #f8f9fa;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #6c757d;
        font-weight: 500;
    }

    .info-value {
        font-weight: 600;
        color: #212529;
    }

    .info-value.highlight {
        color: #667eea;
        font-size: 18px;
    }

    /* Блок обмена */
    .exchange-section {
        background: #e8f4ff;
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .exchange-text {
        font-size: 16px;
        color: #0369a1;
        font-weight: 500;
    }

    .exchange-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 32px;
        border-radius: 100px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .exchange-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    /* Условия использования */
    .terms-section {
        margin-bottom: 24px;
    }

    .terms-title {
        font-size: 18px;
        font-weight: 600;
        color: #212529;
        margin-bottom: 16px;
    }

    .terms-list {
        list-style: none;
    }

    .terms-item {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
        color: #495057;
        font-size: 14px;
        line-height: 1.6;
    }

    .terms-number {
        background: #e9ecef;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        color: #495057;
        flex-shrink: 0;
    }

    .terms-text {
        flex: 1;
    }

    /* Футер */
    .certificate-footer {
        border-top: 1px solid #e9ecef;
        padding: 24px;
        text-align: center;
    }

    .footer-links {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .footer-link {
        color: #667eea;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }

    .footer-link:hover {
        text-decoration: underline;
    }

    .support-info {
        color: #6c757d;
        font-size: 13px;
        line-height: 1.6;
    }

    .support-info a {
        color: #667eea;
        text-decoration: none;
    }

    .support-info a:hover {
        text-decoration: underline;
    }

    /* Адаптивность */
    @media (max-width: 480px) {
        .certificate-header {
            padding: 24px 16px;
        }

        .price {
            font-size: 36px;
        }

        .card-title {
            font-size: 20px;
        }

        .certificate-content {
            padding: 24px 16px;
        }

        .exchange-section {
            flex-direction: column;
            gap: 16px;
            text-align: center;
        }

        .exchange-button {
            width: 100%;
        }

        .footer-links {
            flex-direction: column;
            gap: 12px;
        }
    }
</style>
<div class="certificate-card">
    <!-- Header -->
    <div class="certificate-header">
        <div class="exchange-badge">
            ⚡ ОБМЕН НА ИЗВЕСТНЫЕ БРЕНДЫ НА GIFTERY.UK
        </div>
        <div class="price">
            10 <small>Br</small>
        </div>
        <div class="card-title">
            Giftery Card
        </div>
        <div class="card-subtitle">
            универсальная подарочная карта
        </div>
    </div>

    <!-- Content -->
    <div class="certificate-content">
        <!-- Информация о сертификате -->
        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Номер сертификата</span>
                <span class="info-value">17062-413-985-377</span>
            </div>
            <div class="info-row">
                <span class="info-label">ПИН</span>
                <span class="info-value highlight">3008</span>
            </div>
            <div class="info-row">
                <span class="info-label">Действителен до</span>
                <span class="info-value">27.10.2026</span>
            </div>
            <div class="info-row">
                <span class="info-label">Номер заказа</span>
                <span class="info-value">8595747-1061360</span>
            </div>
        </div>

        <!-- Блок обмена -->
        <div class="exchange-section">
            <span class="exchange-text">Обмен на подарочного сертификата</span>
            <button class="exchange-button">Обменять</button>
        </div>

        <!-- Условия использования -->
        <div class="terms-section">
            <h3 class="terms-title">Условия использования</h3>
            <ul class="terms-list">
                <li class="terms-item">
                    <span class="terms-number">1</span>
                    <span class="terms-text">Универсальный подарочный сертификат «Giftery Card» необходимо обменять через Сайт <a href="https://www.giftery.ru/dopem/" style="color: #667eea; text-decoration: none;">https://www.giftery.ru/dopem/</a> на один или несколько Сертификатов Партнеров, входящих в универсальный подарочный сертификат «Giftery Card». Остаток баланса Giftery Card не сгорает до окончания срока действия, держатель может израсходовать его полностью или частично и вернуться к выбору позже.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-number">2</span>
                    <span class="terms-text">Сертификат действует с момента приобретения в течение 12 месяцев. Срок действия сертификата указан на сертификате.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-number">3</span>
                    <span class="terms-text">Если в течение срока действия универсального подарочного сертификата «Giftery Card» не будет произведен обмен, обязательства сторон по его заключению прекращаются.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-number">4</span>
                    <span class="terms-text">Универсальный подарочный сертификат «Giftery Card» не является ценной бумагой и не подлежит обмену на денежные средства.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-number">5</span>
                    <span class="terms-text">Универсальный подарочный сертификат можно передавать любым лицам по своему усмотрению. При передаче универсального подарочного сертификата третьим лицам владелец универсального подарочного сертификата обязан проинформировать лиц, получающих универсальный подарочный сертификат, об условиях обмена универсального подарочного сертификата «Giftery Card» на сертификат Партнеров, входящих в универсальный подарочный сертификат «Giftery Card». В случае нарушения этой обязанности владельцами универсального подарочного сертификата «Giftery Card» ООО «Диджитал Гриф Карл», по претензиям, связанным с отсутствием вышеуказанной информации, ответственность несет.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-number">6</span>
                    <span class="terms-text">Универсальный подарочный сертификат возврату не подлежит.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-number">7</span>
                    <span class="terms-text">Список Партнеров для обмена универсального подарочного сертификата «Giftery Card» указан на странице сертификата на сайте https://www.giftery.ru/foto/certificat/.</span>
                </li>
                <li class="terms-item">
                    <span class="terms-number">8</span>
                    <span class="terms-text">Дополнительную информацию об универсальном подарочном сертификате «Giftery Card» Клиент может получить на сайте https://www.giftery.ru/foto/certificat/ или по телефону +375 (44) 750-50-37.</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <div class="certificate-footer">
        <div class="footer-links">
            <a href="https://www.giftery.ru" class="footer-link">Разработано Giftery</a>
            <a href="#" class="footer-link">Служба поддержки</a>
        </div>
        <div class="support-info">
            c 8:00 до 20:00, email: <a href="mailto:support@giftery.ru">support@giftery.ru</a><br>
            <a href="tel:+375447505037">+375 (44) 750-50-37</a>, сайт <a href="https://www.giftery.ru">https://www.giftery.ru</a>
        </div>
    </div>
</div>
