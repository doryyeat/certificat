<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\GiftCertificate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function process(Request $request, Order $order)
    {
        // Проверяем, что заказ существует
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found');
        }

        $validated = $request->validate([
            'card_number' => 'required|string',
            'expiry_date' => 'required|string',
            'cvv' => 'required|string',
            'card_holder' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Имитация обработки платежа
            $paymentResult = $this->mockPaymentProcessing($validated, $order);

            if (!$paymentResult['success']) {
                return redirect()->back()->with('error', $paymentResult['message'] ?? 'Payment failed');
            }

            // Создаем запись о покупке
            $purchase = Purchase::create([
                'payment_id' => $paymentResult['transaction_id'],
                'user_id' => auth()->id(),
                'customer_email' => auth()->user()->email,
                'customer_name' => auth()->user()->name,
                'amount' => $order->total_amount,
                'commission' => 0,
                'payment_method' => 'card',
                'payment_system' => 'mock',
                'status' => 'completed',
                'payment_data' => [
                    'card_last4' => substr(preg_replace('/\s+/', '', $validated['card_number']), -4),
                    'card_holder' => $validated['card_holder']
                ],
                'paid_at' => now(),
            ]);

            // Обновляем статус заказа
            $order->update([
                'status' => Order::STATUS_PAID,
                'paid_at' => now(),
                'payment_id' => $paymentResult['transaction_id'],
            ]);

            // Создаем сертификаты для каждого товара в заказе
            $certificates = [];
            foreach ($order->items as $item) {
                $certificate = $this->createCertificateFromOrderItem($item, $order, $purchase);
                $certificates[] = $certificate;

                // Обновляем order_item с id созданного сертификата
                $item->update(['gift_certificate_id' => $certificate->id]);

                // Привязываем сертификат к заказу
                $order->giftCertificates()->attach($certificate->id, [
                    'amount_applied' => $certificate->amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // Отправляем сертификаты на email
            $emailSent = $this->sendCertificatesByEmail($certificates, $order);

            // Сохраняем в сессию данные для страницы успеха
            session()->flash('payment_success', true);
            session()->flash('certificates', $certificates);
            session()->flash('order', $order);

            // Перенаправляем на страницу успеха
            return redirect()->route('payment.success');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    public function success()
    {
        $certificates = session('certificates', []);
        $order = session('order', null);

        return Inertia::render('Payment/Success', [
            'certificates' => $certificates,
            'order' => $order,
        ]);
    }

    private function mockPaymentProcessing(array $paymentData, Order $order): array
    {
        $cardNumber = preg_replace('/\s+/', '', $paymentData['card_number']);

        // Тестовые карты для успешной оплаты
        $testCards = ['4111111111111111', '5555555555554444', '378282246310005'];

        if (in_array($cardNumber, $testCards)) {
            return [
                'success' => true,
                'transaction_id' => 'TXN_' . Str::random(10),
                'message' => 'Payment approved'
            ];
        }

        if (strlen($cardNumber) >= 13 && strlen($cardNumber) <= 19 && strlen($paymentData['cvv']) === 3) {
            return [
                'success' => true,
                'transaction_id' => 'TXN_' . Str::random(10),
                'message' => 'Payment approved'
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid card details. Use test card: 4111111111111111'
        ];
    }

    private function createCertificateFromOrderItem(OrderItem $item, Order $order, Purchase $purchase): GiftCertificate
    {
        $product = $item->product;

        $code = $this->generateUniqueCode();
        $validityDays = $product->validity_days ?? 365;

        // Получаем данные получателя из сессии или заказа
        $recipientEmail = session('recipient_email', $order->recipient_email ?? auth()->user()->email);
        $recipientName = session('recipient_name', $order->recipient_name ?? auth()->user()->name);
        $message = session('gift_message', $order->message ?? null);

        $certificate = GiftCertificate::create([
            'organization_id' => $product->organization_id ?? $order->organization_id,
            'store_id' => $product->store_id ?? null,
            'template_id' => $product->template_id ?? null,
            'code' => $code,
            'title' => $item->name,
            'amount' => $item->price,
            'balance' => $item->price,
            'currency' => 'BYN',
            'category' => $product->category ?? GiftCertificate::CATEGORY_SERVICES,
            'validity_days' => $validityDays,
            'terms_of_use' => $product->terms_of_use ?? null,
            'status' => GiftCertificate::STATUS_ACTIVE,
            'expires_at' => now()->addDays($validityDays),
            'recipient_name' => $recipientName,
            'recipient_email' => $recipientEmail,
            'notes' => $message,
            'created_by' => auth()->id(),
        ]);

        return $certificate;
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (GiftCertificate::where('code', $code)->exists());

        return $code;
    }

    private function sendCertificatesByEmail(array $certificates, Order $order): bool
    {
        $recipientEmail = session('recipient_email', $order->recipient_email ?? auth()->user()->email);
        $recipientName = session('recipient_name', $order->recipient_name ?? auth()->user()->name);
        $giftMessage = session('gift_message', $order->message ?? null);

        if (!$recipientEmail) {
            Log::warning('No recipient email for certificate');
            return false;
        }

        try {
            // Создаем представление для email
            $html = $this->generateCertificateEmailHtml($certificates, $recipientName, $giftMessage, $order);

            Mail::send([], [], function ($message) use ($recipientEmail, $recipientName, $html) {
                $message->to($recipientEmail, $recipientName)
                    ->subject('Ваш подарочный сертификат')
                    ->html($html);
            });

            Log::info('Certificate email sent', ['to' => $recipientEmail, 'certificates' => count($certificates)]);
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send certificate email', [
                'recipient_email' => $recipientEmail,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function generateCertificateEmailHtml(array $certificates, string $recipientName, ?string $giftMessage, Order $order): string
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Подарочный сертификат</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { text-align: center; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px 10px 0 0; }
                .certificate { background: #f9f9f9; border: 1px solid #ddd; border-radius: 10px; padding: 20px; margin: 20px 0; }
                .code { font-size: 24px; font-weight: bold; text-align: center; letter-spacing: 2px; background: #fff; padding: 10px; border-radius: 5px; }
                .amount { font-size: 32px; color: #4CAF50; font-weight: bold; text-align: center; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #999; }
                .message { background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Подарочный сертификат</h1>
                    <p>Здравствуйте, ' . htmlspecialchars($recipientName) . '!</p>
                </div>';

        if ($giftMessage) {
            $html .= '<div class="message">
                        <strong>💝 Личное сообщение:</strong>
                        <p>' . nl2br(htmlspecialchars($giftMessage)) . '</p>
                      </div>';
        }

        foreach ($certificates as $cert) {
            $html .= '<div class="certificate">
                        <h2>' . htmlspecialchars($cert->title) . '</h2>
                        <div class="amount">' . number_format($cert->amount, 2) . ' BYN</div>
                        <div class="code">' . htmlspecialchars($cert->code) . '</div>
                        <p><strong>Действителен до:</strong> ' . $cert->expires_at->format('d.m.Y') . '</p>
                        <p><strong>Категория:</strong> ' . htmlspecialchars($cert->category) . '</p>
                        ' . ($cert->terms_of_use ? '<p><strong>Условия использования:</strong> ' . htmlspecialchars($cert->terms_of_use) . '</p>' : '') . '
                      </div>';
        }

        $html .= '<div class="footer">
                    <p>Спасибо за покупку!</p>
                    <p>Заказ №' . htmlspecialchars($order->number) . '</p>
                    <small>Это письмо создано автоматически, пожалуйста, не отвечайте на него.</small>
                  </div>
            </div>
        </body>
        </html>';

        return $html;
    }
}
