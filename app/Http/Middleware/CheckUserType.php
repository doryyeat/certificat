<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$types
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$types)
    {
        // Если пользователь не авторизован, перенаправляем на главную
        if (!$request->user()) {
            return redirect()->route('home');
        }

        $user = $request->user();

        // Проверяем, есть ли у пользователя client_type
        if (empty($user->client_type)) {
            return redirect()->route('home')->with('error', 'Не удалось определить тип пользователя');
        }

        // Проверяем, соответствует ли тип пользователя разрешенным
        if (!in_array($user->client_type, $types)) {
            abort(403, 'Доступ запрещен для данного типа пользователя');
        }

        return $next($request);
    }
}
