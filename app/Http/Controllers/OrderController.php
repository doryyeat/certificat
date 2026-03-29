<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GiftCertificate;
use App\Models\GiftCertificateTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $organizationId = $request->user()?->organization_id;

        $orders = Order::query()
            ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
            ->with('customer')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where('number', 'like', '%' . $search . '%')
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        $organizationId = auth()->user()?->organization_id;

        return Inertia::render('Orders/Create', [
            'customers' => Customer::query()
                ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
                ->orderBy('name')
                ->get(),
            'products' => Product::query()
                ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
                ->where('active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'certificate.code' => ['nullable', 'string'],
            'certificate.amount' => ['nullable', 'numeric', 'min:0.01'],
        ]);

        $organizationId = $request->user()?->organization_id;

        return DB::transaction(function () use ($data, $organizationId) {
            $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

            $order = Order::create([
                'organization_id' => $organizationId,
                'customer_id' => $data['customer_id'] ?? null,
                'number' => $orderNumber,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            $totalProducts = 0;

            foreach ($data['items'] as $itemData) {
                $lineTotal = $itemData['price'] * $itemData['quantity'];
                $totalProducts += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'] ?? null,
                    'name' => $itemData['name'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'total' => $lineTotal,
                ]);
            }

            $totalDiscount = 0;

            if (!empty($data['certificate']['code']) && !empty($data['certificate']['amount'])) {
                $certificate = GiftCertificate::query()
                    ->when($organizationId, fn($q) => $q->where('organization_id', $organizationId))
                    ->where('code', $data['certificate']['code'])
                    ->first();

                if ($certificate) {
                    $amountToApply = min($data['certificate']['amount'], $certificate->balance, $totalProducts);

                    if ($amountToApply > 0) {
                        $totalDiscount = $amountToApply;

                        $order->giftCertificates()->attach($certificate->id, [
                            'amount_applied' => $amountToApply,
                        ]);

                        $certificate->balance -= $amountToApply;
                        if ($certificate->balance <= 0) {
                            $certificate->status = GiftCertificate::STATUS_REDEEMED;
                        }
                        $certificate->save();

                        GiftCertificateTransaction::create([
                            'gift_certificate_id' => $certificate->id,
                            'order_id' => $order->id,
                            'type' => GiftCertificateTransaction::TYPE_REDEEM,
                            'amount' => $amountToApply,
                            'description' => 'Redeem for order ' . $order->number,
                        ]);
                    }
                }
            }

            $order->update([
                'total_products' => $totalProducts,
                'total_discount' => $totalDiscount,
                'total_amount' => $totalProducts - $totalDiscount,
            ]);

            return redirect()->route('orders.show', $order)->with('success', 'Заказ создан.');
        });
    }

    public function show(Order $order): Response
    {
        $order->load(['customer', 'items', 'giftCertificates']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }
}

