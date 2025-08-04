<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Services\ApprovalService;

echo "=== FIXING TRAVEL REQUESTS ===\n";

$travelRequests = TravelRequest::where('status', 'in_review')
    ->where('current_approval_level', 0)
    ->get();

echo "Found {$travelRequests->count()} travel requests to fix\n\n";

foreach ($travelRequests as $tr) {
    echo "Fixing Travel Request ID: {$tr->id}\n";
    echo "Before - Status: {$tr->status}, Level: {$tr->current_approval_level}\n";
    
    // Initialize approval workflow
    $approvalService = new ApprovalService();
    $approvalService->initializeApprovalWorkflow($tr->fresh());
    
    $tr->refresh();
    echo "After - Status: {$tr->status}, Level: {$tr->current_approval_level}\n";
    echo "---\n";
}

echo "Fix completed!\n"; 
