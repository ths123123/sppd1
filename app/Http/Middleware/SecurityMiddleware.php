<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prevent directory traversal attacks
        $this->preventDirectoryTraversal($request);
        
        // Add security headers
        $response = $next($request);
        
        // Add security headers to response
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        return $response;
    }

    /**
     * Prevent directory traversal attacks
     */
    private function preventDirectoryTraversal(Request $request): void
    {
        $path = $request->path();
        $query = $request->getQueryString();
        
        // Check for directory traversal patterns
        $suspiciousPatterns = [
            '../',
            '..\\',
            '..%2f',
            '..%5c',
            '%2e%2e%2f',
            '%2e%2e%5c',
            '....//',
            '....\\\\',
        ];
        
        $fullPath = $path . ($query ? '?' . $query : '');
        
        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($fullPath, $pattern) !== false) {
                abort(404, 'Invalid path');
            }
        }
        
        // Check for null byte injection
        if (strpos($fullPath, '%00') !== false) {
            abort(404, 'Invalid path');
        }
    }
} 