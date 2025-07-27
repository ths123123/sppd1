<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserProtectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log all user-related actions for audit trail
        if ($request->route() && str_contains($request->route()->uri(), 'user')) {
            Log::info('User action logged', [
                'user_id' => Auth::id(),
                'action' => $request->method(),
                'route' => $request->route()->uri(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()
            ]);
        }
        
        // Prevent deletion of admin users
        if ($request->isMethod('delete') && str_contains($request->route()->uri(), 'user')) {
            $targetUserId = $request->route('user') ?? $request->get('user_id');
            
            if ($targetUserId) {
                $targetUser = \App\Models\User::find($targetUserId);
                
                if ($targetUser && $targetUser->role === 'admin') {
                    // Count total active admin users
                    $totalAdmins = \App\Models\User::where('role', 'admin')
                        ->where('is_active', true)
                        ->count();
                    
                    // Prevent deletion if this is the last admin
                    if ($totalAdmins <= 1) {
                        Log::warning('Attempted to delete last admin user', [
                            'user_id' => Auth::id(),
                            'target_user_id' => $targetUserId,
                            'ip' => $request->ip()
                        ]);
                        
                        return response()->json([
                            'error' => 'Cannot delete the last admin user',
                            'message' => 'At least one admin user must remain active'
                        ], 403);
                    }
                }
            }
        }
        
        // Prevent unauthorized user modifications
        if ($request->isMethod('put') || $request->isMethod('patch')) {
            if (str_contains($request->route()->uri(), 'user')) {
                $targetUserId = $request->route('user') ?? $request->get('user_id');
                
                if ($targetUserId && Auth::id() != $targetUserId) {
                    // Only admins can modify other users
                    if (!Auth::check() || Auth::user()->role !== 'admin') {
                        Log::warning('Unauthorized user modification attempt', [
                            'user_id' => Auth::id(),
                            'target_user_id' => $targetUserId,
                            'ip' => $request->ip()
                        ]);
                        
                        return response()->json([
                            'error' => 'Unauthorized access',
                            'message' => 'You can only modify your own account'
                        ], 403);
                    }
                }
            }
        }
        
        return $next($request);
    }
}
