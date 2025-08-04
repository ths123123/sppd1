<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;

echo "=== TESTING REVISION MESSAGE ===\n\n";

$travelRequest = TravelRequest::find(22);
if ($travelRequest) {
    echo "Travel Request ID: {$travelRequest->id}\n";
    echo "Status: {$travelRequest->status}\n";
    
    $latestRevision = $travelRequest->approvals()
        ->whereIn('status', ['revision', 'revision_minor'])
        ->orderBy('created_at', 'desc')
        ->first();
    
    if ($latestRevision) {
        echo "✅ Found revision approval!\n";
        echo "Approval ID: {$latestRevision->id}\n";
        echo "Status: {$latestRevision->status}\n";
        echo "Comments: {$latestRevision->comments}\n";
        echo "Approver: " . ($latestRevision->approver->name ?? 'Unknown') . "\n";
        echo "Created At: {$latestRevision->created_at}\n";
    } else {
        echo "❌ No revision approval found\n";
    }
} else {
    echo "❌ Travel Request not found\n";
} 
