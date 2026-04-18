<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TariffController extends Controller
{
    private const PLANS = [
        'free' => ['label' => 'Free', 'price_byn' => 0, 'commission' => '3%'],
        'start' => ['label' => 'Start', 'price_byn' => 29, 'commission' => '2%'],
        'pro' => ['label' => 'Pro', 'price_byn' => 79, 'commission' => '1,5%'],
    ];

    public function show(Request $request): Response
    {
        $organization = $request->user()->organization;

        return Inertia::render('Business/Tariff', [
            'currentPlan' => $organization?->plan_name ?? 'free',
            'plans' => self::PLANS,
            'feePercent' => $organization?->transactionFeePercent() ?? 3.0,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;
        if (! $organization) {
            return back()->withErrors(['plan' => 'Организация не найдена.']);
        }

        $data = $request->validate([
            'plan_name' => ['required', 'string', 'in:free,start,pro'],
        ]);

        if (in_array($data['plan_name'], ['start', 'pro'], true)) {
            // Заглушка ТЗ: в проде здесь редирект на платёжный шлюз
            $request->session()->flash(
                'message',
                'В демо-версии смена платного тарифа применяется без оплаты. В продакшене подключите агрегатор (webhook + REST).',
            );
        }

        $organization->update(['plan_name' => $data['plan_name']]);

        return redirect()->route('business.tariff.show')->with('success', 'Тариф обновлён.');
    }
}
