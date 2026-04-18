<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GiftCertificate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CertificateController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Показать доступные сертификаты для покупки
     */
    public function index(Request $request)
    {
        $certificates = GiftCertificate::with(['organization', 'template'])
            ->where('status', GiftCertificate::STATUS_ACTIVE)
            ->where('balance', '>', 0)
            ->when($request->search, function ($query, $search) {
                $query->whereHas('organization', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($request->min_price, function ($query, $price) {
                $query->where('amount', '>=', $price);
            })
            ->when($request->max_price, function ($query, $price) {
                $query->where('amount', '<=', $price);
            })
            ->paginate(12);

        return Inertia::render('Client/Certificates/Index', [
            'certificates' => $certificates,
            'filters' => $request->only(['search', 'min_price', 'max_price']),
        ]);
    }

    /**
     * Показать детали сертификата
     */
    public function show(GiftCertificate $certificate)
    {
        if ($certificate->status !== GiftCertificate::STATUS_ACTIVE) {
            return redirect()->route('client.certificates.index')
                ->with('error', 'Сертификат недоступен для покупки');
        }

        return Inertia::render('Client/Certificates/Show', [
            'certificate' => $certificate->load(['organization', 'template']),
        ]);
    }

    /**
     * Купить сертификат
     */
    public function purchase(Request $request, GiftCertificate $certificate)
    {

        $request->validate([
            'payment_method' => 'required|in:card,cash,apple_pay,google_pay',
            'recipient_email' => 'required|email',
            'recipient_name' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:500',
        ]);
        try {

            // Создаем заказ
            $order = Order::create([
                'organization_id' => $certificate->organization_id,
                'user_id' => auth()->id(),
                'number' => 'ORD-' . strtoupper(uniqid()),
                'status' => Order::STATUS_PENDING,
                'total_amount' => $certificate->amount,
            ]);
            // Добавляем товар в заказ

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'gift_certificate_id' => $certificate->id,
                'name' => 'Подарочный сертификат',
                'price' => $certificate->amount,
                'quantity' => 1,
                'total' => $certificate->amount,
            ]);


            // Передаем данные в страницу оплаты
            return Inertia::render('Payment/PaymentProcess', [
                'order' => $order->load('items'),
                'certificate' => $certificate,
                'amount' => $certificate->amount,
                'recipient_email' => $request->recipient_email,
                'recipient_name' => $request->recipient_name,
                'message' => $request->message,
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при создании заказа: ' . $e->getMessage());
        }
    }

    /**
     * Мои купленные сертификаты
     */
    public function myCertificates()
    {
        $orders = Order::with(['items', 'giftCertificates'])
            ->where('user_id', auth()->id())
            ->where('status', Order::STATUS_PAID)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Client/Certificates/MyCertificates', [
            'orders' => $orders,
        ]);
    }

    /**
     * Показать конкретный купленный сертификат
     */
    public function showPurchased(GiftCertificate $certificate)
    {
        // Проверяем, принадлежит ли сертификат пользователю
        $order = Order::where('user_id', auth()->id())
            ->whereHas('giftCertificates', function ($query) use ($certificate) {
                $query->where('gift_certificate_id', $certificate->id);
            })
            ->first();

        if (!$order) {
            abort(403);
        }

        return Inertia::render('Client/Certificates/ShowPurchased', [
            'certificate' => $certificate->load(['organization', 'transactions']),
            'order' => $order,
        ]);
    }
}
