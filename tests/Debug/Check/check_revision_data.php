<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\Approval;

echo "=== CHECKING REVISION DATA ===\n\n";

// Check all approvals
$approvals = Approval::with(['travelRequest', 'approver'])->get();
echo "Total approvals: {$approvals->count()}\n\n";

foreach ($approvals as $approval) {
    echo "Approval ID: {$approval->id}\n";
    echo "Travel Request ID: {$approval->travel_request_id}\n";
    echo "Status: {$approval->status}\n";
    echo "Role: {$approval->role}\n";
    echo "Comments: " . ($approval->comments ?? 'NULL') . "\n";
    echo "Revision Notes: " . (json_encode($approval->revision_notes) ?? 'NULL') . "\n";
    echo "Approver: " . ($approval->approver->name ?? 'NULL') . "\n";
    echo "Created At: {$approval->created_at}\n";
    echo "---\n";
}

// Check travel requests with revision status
echo "\n=== TRAVEL REQUESTS WITH REVISION STATUS ===\n";
$revisionRequests = TravelRequest::where('status', 'revision')->get();
echo "Total revision requests: {$revisionRequests->count()}\n";

foreach ($revisionRequests as $request) {
    echo "ID: {$request->id}, Status: {$request->status}\n";
} 