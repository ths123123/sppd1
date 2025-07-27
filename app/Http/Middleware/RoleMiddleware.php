<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, \Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized - Please login');
        }

        $user = auth()->user();
        
        // Admin has access to everything
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Check if user's role is in the allowed roles
        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized - Insufficient privileges');
        }

        return $next($request);
    }
}
