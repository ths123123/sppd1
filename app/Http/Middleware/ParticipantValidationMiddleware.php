<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ParticipantValidationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Log participant data for debugging
        if ($request->has('participants')) {
            Log::info('Participant data received', [
                'participants' => $request->participants,
                'type' => gettype($request->participants),
                'url' => $request->url()
            ]);
        }

        $response = $next($request);

        // Handle validation errors gracefully
        if ($response->getStatusCode() === 422) {
            $errors = $response->getData()->errors ?? [];
            
            // Check for participant-related errors
            $participantErrors = collect($errors)->filter(function ($error, $key) {
                return str_contains($key, 'participants');
            });

            if ($participantErrors->isNotEmpty()) {
                Log::warning('Participant validation errors detected', [
                    'errors' => $participantErrors->toArray(),
                    'url' => $request->url(),
                    'method' => $request->method()
                ]);
            }
        }

        return $response;
    }
} 