<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\TravelRequest;
use App\Observers\TravelRequestObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TravelRequest::observe(TravelRequestObserver::class);
        
        // Add User Observer for monitoring user changes
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        
        // Cek apakah tabel activity_logs kosong dan ada data travel_requests
        try {
            if (\Illuminate\Support\Facades\DB::table('activity_logs')->count() === 0 && 
                \Illuminate\Support\Facades\DB::table('travel_requests')->count() > 0) {
                // Jalankan command untuk generate activity logs
                \Illuminate\Support\Facades\Log::info('Generating activity logs from existing travel requests on application boot');
                \Illuminate\Support\Facades\Artisan::call('activity:generate');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error checking or generating activity logs: ' . $e->getMessage());
        }

        // --- AUTO CHECK & CREATE STORAGE SYMLINK ---
        if (app()->runningInConsole() === false) { // Only run in web context
            $publicStorage = public_path('storage');
            $target = storage_path('app/public');
            if (!is_link($publicStorage) && !file_exists($publicStorage)) {
                try {
                    symlink($target, $publicStorage);
                } catch (\Exception $e) {
                    // Optionally log error
                    \Log::warning('Failed to create storage symlink: ' . $e->getMessage());
                }
            }
        }
        // --- END AUTO SYMLINK ---
    }
}
