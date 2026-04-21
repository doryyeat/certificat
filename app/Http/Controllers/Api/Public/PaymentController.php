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
use App\Mail\GiftCertificateMail;

class PaymentController extends Controller
{
    public function process(Request $request, Order $order)
    {
        // Проверяем, что заказ существует
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found');
        }
        $order->load('items');

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

            // Подтверждаем покупку сертификатов, которые уже добавлены в заказ
            $certificates = [];
            foreach ($order->items as $item) {
                $certificate = $this->finalizeCertificatePurchase($item, $order);
                $certificates[] = $certificate;

                // Привязываем сертификат к заказу, если ещё не привязан
                if (! $order->giftCertificates()->where('gift_certificate_id', $certificate->id)->exists()) {
                    $order->giftCertificates()->attach($certificate->id, [
                        'amount_applied' => $certificate->amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Отправляем сертификаты на email
            $emailSent = $this->sendCertificatesByEmail($certificates, $order);
            session()->flash('mail_sent', (bool) $emailSent);
            session()->flash('mail_transport', (string) config('mail.default'));

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
        $mailSent = session('mail_sent', null);
        $mailTransport = session('mail_transport', null);

        return Inertia::render('Payment/Success', [
            'certificates' => $certificates,
            'order' => $order,
            'mail_sent' => $mailSent,
            'mail_transport' => $mailTransport,
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

    private function finalizeCertificatePurchase(OrderItem $item, Order $order): GiftCertificate
    {
        if (! $item->gift_certificate_id) {
            throw new \RuntimeException('Order item does not reference a gift certificate');
        }

        $certificate = GiftCertificate::query()->lockForUpdate()->findOrFail($item->gift_certificate_id);

        if ((int) $certificate->organization_id !== (int) $order->organization_id) {
            throw new \RuntimeException('Gift certificate does not belong to this order organization');
        }

        if ($certificate->status !== GiftCertificate::STATUS_ACTIVE || $certificate->balance <= 0) {
            throw new \RuntimeException('Gift certificate is not available for purchase');
        }

        // Если сертификат уже куплен этим же заказом — идемпотентно возвращаем
        if ($certificate->sold_at) {
            if ((int) $certificate->sold_order_id === (int) $order->id) {
                return $certificate;
            }

            // Если куплен другим заказом — запрещаем повторную продажу
            throw new \RuntimeException('Gift certificate has already been sold to another customer');
        }

        $certificate->recipient_email = $order->recipient_email ?? auth()->user()->email;
        $certificate->recipient_name = $order->recipient_name ?? auth()->user()->name;
        $certificate->notes = $order->message ?? null;
        $certificate->sold_at = now();
        $certificate->sold_order_id = $order->id;
        $certificate->save();

        return $certificate;
    }

    private function sendCertificatesByEmail(array $certificates, Order $order): bool
    {
        $recipientEmail = $order->recipient_email ?? auth()->user()->email;
        $recipientName = $order->recipient_name ?? auth()->user()->name;
        $giftMessage = $order->message ?? null;

        if (!$recipientEmail) {
            Log::warning('No recipient email for certificate');
            return false;
        }

        try {
            Mail::to($recipientEmail, $recipientName)->send(
                new GiftCertificateMail($certificates, $recipientName, $giftMessage, $order->number)
            );

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
}
