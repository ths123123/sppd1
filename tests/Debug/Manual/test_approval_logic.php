<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use App\Models\Approval;

echo "=== APPROVAL LOGIC TEST ===\n\n";

// Test 1: Check Sekretaris can approve as participant
$sekretaris = User::where('role', 'sekretaris')->first();
if ($sekretaris) {
    echo "✅ Sekretaris found: {$sekretaris->name}\n";
    
    // Find a travel request where sekretaris is participant
    $travelRequest = TravelRequest::with('participants')
        ->whereHas('participants', function($query) use ($sekretaris) {
            $query->where('user_id', $sekretaris->id);
        })
        ->first();
    
    if ($travelRequest) {
        echo "✅ Found travel request where sekretaris is participant\n";
        echo "   Travel Request ID: {$travelRequest->id}\n";
        echo "   Status: {$travelRequest->status}\n";
        echo "   Participants: " . $travelRequest->participants->count() . "\n";
        
        // Check if sekretaris is participant
        $isParticipant = $travelRequest->participants->contains('id', $sekretaris->id);
        echo "   Sekretaris is participant: " . ($isParticipant ? 'YES' : 'NO') . "\n";
        
        if ($isParticipant) {
            echo "✅ Logic: Sekretaris can approve as participant (no Plt/Plh required)\n";
        }
    } else {
        echo "ℹ️  No travel request found where sekretaris is participant\n";
    }
} else {
    echo "❌ Sekretaris not found\n";
}

// Test 2: Check PPK can approve as participant
$ppk = User::where('role', 'ppk')->first();
if ($ppk) {
    echo "\n✅ PPK found: {$ppk->name}\n";
    
    // Find a travel request where PPK is participant
    $travelRequest = TravelRequest::with('participants')
        ->whereHas('participants', function($query) use ($ppk) {
            $query->where('user_id', $ppk->id);
        })
        ->first();
    
    if ($travelRequest) {
        echo "✅ Found travel request where PPK is participant\n";
        echo "   Travel Request ID: {$travelRequest->id}\n";
        echo "   Status: {$travelRequest->status}\n";
        
        // Check if PPK is participant
        $isParticipant = $travelRequest->participants->contains('id', $ppk->id);
        echo "   PPK is participant: " . ($isParticipant ? 'YES' : 'NO') . "\n";
        
        if ($isParticipant) {
            echo "✅ Logic: PPK can approve as participant (no Plt/Plh required)\n";
        }
    } else {
        echo "ℹ️  No travel request found where PPK is participant\n";
    }
} else {
    echo "❌ PPK not found\n";
}

// Test 3: Check approval workflow
echo "\n=== APPROVAL WORKFLOW TEST ===\n";
$completedRequests = TravelRequest::where('status', 'completed')->count();
$revisionRequests = TravelRequest::where('status', 'revision')->count();
$rejectedRequests = TravelRequest::where('status', 'rejected')->count();

echo "✅ Completed requests: {$completedRequests}\n";
echo "✅ Revision requests: {$revisionRequests}\n";
echo "✅ Rejected requests: {$rejectedRequests}\n";

// Test 4: Check approval levels
$approvals = Approval::with(['travelRequest', 'approver'])->get();
echo "\n=== APPROVAL DETAILS ===\n";
foreach ($approvals as $approval) {
    echo "Approval ID: {$approval->id}\n";
    echo "  Travel Request: {$approval->travel_request_id}\n";
    echo "  Status: {$approval->status}\n";
    echo "  Role: {$approval->role}\n";
    echo "  Approver: " . ($approval->approver->name ?? 'N/A') . "\n";
    echo "  Comments: " . ($approval->comments ?? 'N/A') . "\n";
    echo "  ---\n";
}

echo "\n=== TEST COMPLETED ===\n"; 