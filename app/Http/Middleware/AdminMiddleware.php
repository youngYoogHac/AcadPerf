<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || $user->role?->name !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'Доступ только для администраторов.');
        }

        return $next($request);
    }
}