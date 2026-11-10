<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('lendora.kiosk_token', 'lendora-kiosk');

        if ($request->session()->get('kiosk.authorized') === true) {
            return $next($request);
        }

        if (hash_equals($token, (string) $request->query('token', ''))) {
            $request->session()->put('kiosk.authorized', true);

            return $next($request);
        }

        abort(403, __('messages.kiosk.token_required'));
    }
}
