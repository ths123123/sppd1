<?php

require_once '../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once '../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ActivityLog;
use App\Models\TravelRequest;

echo "=== CHECKING ACTIVITY LOG DETAILS ===\n";
echo "Total Activity Logs: " . ActivityLog::count() . "\n\n";

if (ActivityLog::count() > 0) {
    echo "Recent 10 Activity Logs with Details:\n";
    echo str_repeat("=", 80) . "\n";

    ActivityLog::with('user')->latest()->take(10)->get()->each(function($log) {
        echo "ID: {$log->id}\n";
        echo "Action: {$log->action}\n";
        echo "User: " . ($log->user ? $log->user->name : 'System') . "\n";
        echo "Created: {$log->created_at}\n";

        if ($log->details) {
            echo "Details:\n";
            foreach ($log->details as $key => $value) {
                if ($key === 'description') {
                    echo "  {$key}: {$value}\n";
                }
            }
        }

        echo str_repeat("-", 40) . "\n";
    });
} else {
    echo "No activity logs found!\n";
}

echo "\n=== CHECKING TRAVEL REQUESTS STATUS ===\n";
echo "Total Travel Requests: " . TravelRequest::count() . "\n\n";

if (TravelRequest::count() > 0) {
    echo "Travel Requests by Status:\n";
    echo str_repeat("=", 50) . "\n";

    $statuses = TravelRequest::selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->get();

    foreach ($statuses as $status) {
        echo "Status: {$status->status} - Count: {$status->count}\n";
    }

    echo "\nRecent 5 Travel Requests:\n";
    echo str_repeat("=", 50) . "\n";

    TravelRequest::with(['user', 'approvals.approver'])->latest()->take(5)->get()->each(function($tr) {
        echo "ID: {$tr->id}\n";
        echo "Kode: {$tr->kode_sppd}\n";
        echo "Status: {$tr->status}\n";
        echo "Current Approval Level: {$tr->current_approval_level}\n";
        echo "User: " . ($tr->user ? $tr->user->name : 'Unknown') . "\n";
        echo "Tujuan: {$tr->tujuan}\n";

        if ($tr->approvals->count() > 0) {
            echo "Approvals:\n";
            foreach ($tr->approvals as $approval) {
                $approverName = $approval->approver ? $approval->approver->name : 'Unknown';
                $approverRole = $approval->approver ? $approval->approver->role : 'Unknown';
                echo "  - Level {$approval->level}: {$approverName} ({$approverRole}) - {$approval->status}\n";
            }
        }

        echo str_repeat("-", 30) . "\n";
    });
} else {
    echo "No travel requests found!\n";
}

echo "\n=== CHECKING DASHBOARD ACTIVITIES ===\n";
try {
    $dashboardService = app('App\Services\DashboardService');
    $activities = $dashboardService->getFormattedRecentActivities(5);
    echo "Formatted Activities Count: " . count($activities) . "\n\n";

    if (count($activities) > 0) {
        echo "Dashboard Activities:\n";
        echo str_repeat("=", 60) . "\n";

        foreach ($activities as $index => $activity) {
            echo ($index + 1) . ". {$activity['description']}\n";
            echo "   Status: {$activity['status']}\n";
            echo "   Approver: " . ($activity['approver_name'] ?? 'N/A') . "\n";
            echo "   Approver Role: " . ($activity['approver_role'] ?? 'N/A') . "\n";
            echo "   Time: {$activity['time_ago']}\n";
            echo str_repeat("-", 40) . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error in DashboardService: " . $e->getMessage() . "\n";
}

echo "\n=== END ===\n";
