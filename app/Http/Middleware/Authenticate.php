<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Redirect ke halaman login dengan pesan
            session()->flash('message', 'Silakan login terlebih dahulu untuk mengakses sistem SPPD KPU Kabupaten Cirebon.');
            return route('login');
        }

        return null;
    }
}
