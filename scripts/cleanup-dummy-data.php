<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\Notification;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧹 Starting cleanup of dummy data...\n\n";

try {
    // Start transaction
    DB::beginTransaction();
    
    // 1. Delete ALL travel requests
    $allTravelRequests = TravelRequest::all();
    $travelRequestCount = $allTravelRequests->count();
    
    if ($travelRequestCount > 0) {
        // Delete related approvals first
        $travelRequestIds = $allTravelRequests->pluck('id')->toArray();
        $approvalCount = Approval::whereIn('travel_request_id', $travelRequestIds)->delete();
        
        // Delete related notifications
        $notificationCount = Notification::whereIn('travel_request_id', $travelRequestIds)->delete();
        
        // Delete travel request participants
        $participantCount = DB::table('travel_request_participants')->whereIn('travel_request_id', $travelRequestIds)->delete();
        
        // Delete documents
        $documentCount = DB::table('documents')->whereIn('travel_request_id', $travelRequestIds)->delete();
        
        // Delete travel requests
        $deletedCount = TravelRequest::whereIn('id', $travelRequestIds)->delete();
        
        echo "✅ Deleted {$deletedCount} travel requests\n";
        echo "✅ Deleted {$approvalCount} related approvals\n";
        echo "✅ Deleted {$notificationCount} related notifications\n";
        echo "✅ Deleted {$participantCount} participant records\n";
        echo "✅ Deleted {$documentCount} documents\n";
    } else {
        echo "ℹ️  No travel requests found\n";
    }
    
    // Commit transaction
    DB::commit();
    
    // Show final statistics
    $totalTravelRequests = TravelRequest::count();
    $totalApprovals = Approval::count();
    $totalNotifications = Notification::count();
    
    echo "\n📊 Database Statistics after cleanup:\n";
    echo "   • Travel Requests: {$totalTravelRequests}\n";
    echo "   • Approvals: {$totalApprovals}\n";
    echo "   • Notifications: {$totalNotifications}\n";
    
    echo "\n🎉 Cleanup completed successfully!\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    DB::rollBack();
    
    echo "❌ Error during cleanup: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    exit(1);
} 