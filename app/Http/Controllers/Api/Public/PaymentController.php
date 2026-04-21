<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\GiftCertificate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchasedCertificate;
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
    public function checkout(Request $request, Order $order)
    {
        if ((int) $order->customer_id !== (int) auth()->id()) {
            abort(403);
        }

        if ($order->status === Order::STATUS_PAID) {
            return redirect()->route('client.payment.success');
        }

        $order->load('items');
        $firstItem = $order->items->first();
        $certificate = $firstItem?->gift_certificate_id
            ? GiftCertificate::find($firstItem->gift_certificate_id)
            : null;

        return Inertia::render('Payment/PaymentProcess', [
            'order' => $order,
            'certificate' => $certificate,
            'amount' => (float) $order->total_amount,
            'recipient_email' => $order->recipient_email,
            'recipient_name' => $order->recipient_name,
            'message' => $order->message,
        ]);
    }

    public function process(Request $request, Order $order)
    {
        // Проверяем, что заказ существует
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found');
        }
        if ((int) $order->customer_id !== (int) auth()->id()) {
            abort(403);
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
            try {


                foreach ($order->items as $item) {
                    $certificate = $this->finalizeCertificatePurchase($item, $order);
                    $certificates[] = $certificate;

                    // Привязываем сертификат к заказу, если ещё не привязан
                    if (!$order->giftCertificates()->where('gift_certificate_id', $certificate->id)->exists()) {
                        $order->giftCertificates()->attach($certificate->id, [
                            'amount_applied' => $certificate->amount,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }catch (\Exception $exception){
                dd($exception->getMessage());
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
            return redirect()->route('client.payment.success');

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

    private function finalizeCertificatePurchase(OrderItem $item, Order $order): PurchasedCertificate
    {
        if (!$item->gift_certificate_id) {
            throw new \RuntimeException('Order item does not reference a gift certificate');
        }

        $template = GiftCertificate::query()->lockForUpdate()->findOrFail($item->gift_certificate_id);

        // Проверяем, что это шаблон (не purchased)
        if ($template->source_certificate_id !== null) {
            throw new \RuntimeException('Cannot purchase an already purchased certificate');
        }

        if ($template->status !== GiftCertificate::STATUS_ACTIVE || $template->balance <= 0) {
            throw new \RuntimeException('Gift certificate is not available for purchase');
        }

        // Генерируем УНИКАЛЬНЫЙ код для purchased сертификата
        $code = $this->generateUniquePurchasedCode();

        // Создаем purchased сертификат
        $purchased = PurchasedCertificate::create([
            'organization_id' => $template->organization_id,
            'store_id' => $template->store_id,
            'template_id' => $template->template_id,
            'source_certificate_id' => $template->id,
            'code' => $code, // Уникальный код!
            'title' => $template->title,
            'amount' => $template->amount,
            'balance' => $template->amount,
            'currency' => $template->currency,
            'category' => $template->category,
            'validity_days' => $template->validity_days,
            'terms_of_use' => $template->terms_of_use,
            'status' => PurchasedCertificate::STATUS_ACTIVE,
            'expires_at' => now()->addDays((int) ($template->validity_days ?? 365)),
            'recipient_name' => $order->recipient_name ?? auth()->user()->name,
            'recipient_email' => $order->recipient_email ?? auth()->user()->email,
            'notes' => $order->message ?? null,
            'created_by' => auth()->id(),
            'sold_at' => now(),
            'sold_order_id' => $order->id,
        ]);

        // Создаем транзакцию
        $purchased->transactions()->create([
            'type' => 'issue',
            'amount' => $purchased->amount,
            'description' => 'Issued from template ' . $template->code,
            'balance_after' => $purchased->amount,
        ]);

        // Обновляем item
        $item->gift_certificate_id = $purchased->id;
        $item->save();

        return $purchased;
    }

    private function generateUniquePurchasedCode(): string
    {
        do {
            $code = 'GIFT-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (PurchasedCertificate::where('code', $code)->exists() || GiftCertificate::where('code', $code)->exists());

        return $code;
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
