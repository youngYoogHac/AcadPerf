<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || $user->role?->name !== 'teacher') {
            return redirect()->route('dashboard')->with('error', 'Доступ только для преподавателей.');
        }

        return $next($request);
    }
}