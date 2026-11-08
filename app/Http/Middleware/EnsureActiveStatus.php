<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->status !== 'active') {
            abort(403);
        }

        return $next($request);
    }
}
