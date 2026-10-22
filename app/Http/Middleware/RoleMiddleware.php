<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $allowedRoles = [];

        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, array_map('trim', explode(',', $role)));
        }

        $allowedRoles = array_filter($allowedRoles);

        if (empty($allowedRoles) || !in_array(auth()->user()->role, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
