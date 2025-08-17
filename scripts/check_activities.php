<?php

require_once '../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ActivityLog;
use App\Models\TravelRequest;

echo "=== CHECKING ACTIVITY LOGS ===\n";
echo "Total Activity Logs: " . ActivityLog::count() . "\n";

if (ActivityLog::count() > 0) {
    echo "\nRecent 5 Activity Logs:\n";
    ActivityLog::latest()->take(5)->get()->each(function($log) {
        echo "- ID: {$log->id}, Action: {$log->action}, User: {$log->user_id}, Created: {$log->created_at}\n";
    });
} else {
    echo "\nNo activity logs found!\n";
}

echo "\n=== CHECKING TRAVEL REQUESTS ===\n";
echo "Total Travel Requests: " . TravelRequest::count() . "\n";

if (TravelRequest::count() > 0) {
    echo "\nRecent 3 Travel Requests:\n";
    TravelRequest::latest()->take(3)->get()->each(function($tr) {
        echo "- ID: {$tr->id}, Kode: {$tr->kode_sppd}, Status: {$tr->status}, Created: {$tr->created_at}\n";
    });
} else {
    echo "\nNo travel requests found!\n";
}

echo "\n=== CHECKING DASHBOARD SERVICE ===\n";
try {
    $dashboardService = app('App\Services\DashboardService');
    $activities = $dashboardService->getFormattedRecentActivities(5);
    echo "Formatted Activities Count: " . count($activities) . "\n";
    
    if (count($activities) > 0) {
        echo "\nFirst Activity:\n";
        print_r($activities[0]);
    }
} catch (Exception $e) {
    echo "Error in DashboardService: " . $e->getMessage() . "\n";
}

echo "\n=== END ===\n";
