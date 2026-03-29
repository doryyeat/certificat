<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifiedBusinessMiddleware
{
    /**
     * Проверяет верификацию бизнеса
     */
    public function handle(Request $request, Closure $next)
    {
        $business = $request->user()->business;

        if (!$business->is_verified) {
            return response()->json([
                'message' => 'Business not verified',
                'status' => 'pending_verification',
                'verification_url' => route('api.business.verify')
            ], 403);
        }

        return $next($request);
    }
}
