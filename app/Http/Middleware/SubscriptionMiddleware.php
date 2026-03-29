<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionMiddleware
{
    /**
     * Проверяет подписку бизнеса и доступность API
     */
    public function handle(Request $request, Closure $next, string $required = 'api')
    {
        $user = Auth::user();

        if (!$user || !$user->business) {
            return response()->json([
                'message' => 'Business not found'
            ], 403);
        }

        $business = $user->business;
        $subscription = $business->subscription_details;

        // Проверяем доступ к API в зависимости от тарифа
        if ($required === 'api' && !$subscription['api_enabled']) {
            return response()->json([
                'message' => 'API access not available on current plan',
                'current_plan' => $business->subscription,
                'required_plan' => 'start or pro'
            ], 403);
        }

        // Проверяем лимиты
        if ($required === 'create_certificate' && !$business->canCreateCertificate()) {
            return response()->json([
                'message' => 'Certificate limit reached',
                'limit' => $subscription['certificates_limit'],
                'current' => $business->active_certificates_count,
                'upgrade_url' => route('api.business.subscription.upgrade')
            ], 403);
        }

        return $next($request);
    }
}
