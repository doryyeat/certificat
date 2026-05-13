<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GiftCertificate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchasedCertificate;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
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
        $sort = $request->string('sort')->toString();
        if ($sort === '') {
            $sort = (string) $request->session()->get('catalog_sort', 'default');
        } else {
            $request->session()->put('catalog_sort', $sort);
        }

        $request->validate([
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string', 'in:' . implode(',', GiftCertificate::CATEGORIES)],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:default,price_asc,price_desc,popularity,newest'],
        ]);

        $categories = $request->input('categories', []);

        $certificates = GiftCertificate::query()
            ->with(['organization', 'template', 'store'])
            ->where('gift_certificates.status', GiftCertificate::STATUS_ACTIVE) // ← ИСПРАВЛЕНО: добавлена таблица
            ->where('balance', '>', 0)
            ->whereNull('source_certificate_id')
            ->whereNull('sold_at')
            ->when($request->search, function ($query, $search) {
                $query->whereHas('organization', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when(!empty($categories), fn ($q) => $q->whereIn('category', $categories))
            ->when($request->min_price, function ($query, $price) {
                $query->where('amount', '>=', $price);
            })
            ->when($request->max_price, function ($query, $price) {
                $query->where('amount', '<=', $price);
            })
            ->when($sort === 'price_asc', fn ($q) => $q->orderBy('amount', 'asc'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderBy('amount', 'desc'))
            ->when($sort === 'newest', fn ($q) => $q->orderBy('created_at', 'desc'))
            ->when($sort === 'popularity', function ($q) {
                $since = now()->subDays(30);
                $q->leftJoin('order_items', 'order_items.gift_certificate_id', '=', 'gift_certificates.id')
                    ->leftJoin('orders', function ($join) use ($since) {
                        $join->on('orders.id', '=', 'order_items.order_id')
                            ->where('orders.status', '=', Order::STATUS_PAID) // Здесь тоже лучше указать таблицу
                            ->where('orders.paid_at', '>=', $since);
                    })
                    ->select('gift_certificates.*')
                    ->selectRaw('COUNT(orders.id) as popularity_30d')
                    ->groupBy('gift_certificates.id')
                    ->orderByDesc('popularity_30d')
                    ->orderByDesc('gift_certificates.created_at');
            })
            ->when($sort === 'default', function ($q) {
                $q->join('organizations', 'organizations.id', '=', 'gift_certificates.organization_id')
                    ->select('gift_certificates.*')
                    ->orderByRaw("CASE LOWER(COALESCE(organizations.plan_name, 'free')) WHEN 'pro' THEN 1 WHEN 'start' THEN 2 ELSE 3 END ASC")
                    ->orderByDesc('gift_certificates.created_at');
            })
            ->paginate(12);

        return Inertia::render('Client/Certificates/Index', [
            'certificates' => $certificates,
            'filters' => [
                'search' => $request->search,
                'min_price' => $request->min_price,
                'max_price' => $request->max_price,
                'categories' => $categories,
                'sort' => $sort,
            ],
        ]);
    }
    /**
     * Показать детали сертификата
     */
    public function show(GiftCertificate $certificate)
    {
        if ($certificate->status !== GiftCertificate::STATUS_ACTIVE || $certificate->sold_at || $certificate->source_certificate_id !== null) {
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
    public function purchaseGet(GiftCertificate $certificate)
    {
        return redirect()
            ->route('client.certificates.show', $certificate)
            ->with('error', 'Покупка доступна только через форму (POST). Нажмите «Оплатить» на странице сертификата.');
    }

    public function purchase(Request $request, GiftCertificate $certificate)
    {
        if ($certificate->sold_at) {
            return back()->withErrors(['certificate' => 'Сертификат уже куплен другим клиентом.']);
        }

        $request->validate([
            'payment_method' => 'required|in:card,cash,apple_pay,google_pay',
            'recipient_email' => 'required|email',
            'recipient_name' => 'required|string|max:100',
            'message' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Создаем заказ
            $order = Order::create([
                'organization_id' => $certificate->organization_id,
                'customer_id' => auth()->id(),
                'number' => 'ORD-' . strtoupper(uniqid()),
                'status' => Order::STATUS_PENDING,
                'total_amount' => $certificate->amount,
                'recipient_email' => $request->recipient_email,
                'recipient_name' => $request->recipient_name,
                'message' => $request->message,
            ]);

            // Добавляем товар в заказ
            OrderItem::create([
                'order_id' => $order->id,
                'gift_certificate_id' => $certificate->id,
                'name' => $certificate->title,
                'price' => $certificate->amount,
                'quantity' => 1,
                'total' => $certificate->amount,
            ]);

            DB::commit();

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
            DB::rollBack();
            return back()->with('error', 'Ошибка при создании заказа: ' . $e->getMessage());
        }
    }

    /**
     * Мои купленные сертификаты
     */
    public function myCertificates()
    {
        // Получаем все purchased сертификаты, которые принадлежат пользователю
        $certificates = PurchasedCertificate::with(['store', 'transactions', 'order'])
            ->where('recipient_email', auth()->user()->email)
            ->orWhere('created_by', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Client/Certificates/MyCertificates', [
            'certificates' => $certificates,
        ]);
    }

    /**
     * Показать конкретный купленный сертификат
     */
    public function showPurchased(PurchasedCertificate $certificate)
    {
        // Проверяем, принадлежит ли сертификат пользователю
        if ($certificate->recipient_email !== auth()->user()->email &&
            $certificate->created_by !== auth()->id()) {
            abort(403);
        }

        $certificate->load(['store', 'transactions', 'order', 'sourceCertificate']);

        return Inertia::render('Client/Certificates/ShowPurchased', [
            'certificate' => $certificate,
        ]);
    }
}
