<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecureHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Set secure headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Ensure JSON responses have UTF-8 charset
        if ($response->headers->get('Content-Type') === 'application/json') {
            $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        }
        
        // Set secure cookie attributes
        $response->headers->setCookie($response->headers->getCookies());
        foreach ($response->headers->getCookies() as $cookie) {
            // Selalu set secure=true untuk semua cookie, tidak peduli environment
            $cookie->setSecure(true);
            $cookie->setHttpOnly(true);
            $cookie->setSameSite('lax');
        }
        
        return $response;
    }
} 