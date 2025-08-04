<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\User;
use App\Services\ApprovalService;

echo "=== TESTING REVISION WORKFLOW ===\n\n";

$approvalService = new ApprovalService();

// Get travel request 22
$travelRequest = TravelRequest::with(['approvals', 'user'])->find(22);
if (!$travelRequest) {
    echo "❌ Travel Request 22 not found\n";
    exit;
}

echo "Travel Request ID: {$travelRequest->id}\n";
echo "Status: {$travelRequest->status}\n";
echo "Current Approval Level: {$travelRequest->current_approval_level}\n\n";

// Check previous approvals
$approvedApprovals = $travelRequest->approvals()->where('status', 'approved')->get();
echo "Previous approved levels:\n";
foreach ($approvedApprovals as $approval) {
    echo "- Level: {$approval->level}, Role: {$approval->role}, Approver: {$approval->approver->name}\n";
}

// Test the new workflow logic
echo "\n=== TESTING NEW WORKFLOW LOGIC ===\n";

// Simulate the new workflow initialization
$submitterRole = $travelRequest->user->role;
$flow = $approvalService->getApprovalFlowForSubmitter($submitterRole);

echo "Submitter Role: {$submitterRole}\n";
echo "Approval Flow: " . json_encode($flow) . "\n";

$approvedLevels = $travelRequest->approvals()
    ->where('status', 'approved')
    ->pluck('level')
    ->toArray();

echo "Approved Levels: " . json_encode($approvedLevels) . "\n";

// Determine next level manually
if (empty($approvedLevels)) {
    $nextLevel = 1;
} else {
    $highestApprovedLevel = max($approvedLevels);
    $nextLevel = $highestApprovedLevel + 1;
    
    if (!isset($flow[$nextLevel])) {
        $nextLevel = 1;
    }
}

echo "Next Approval Level: {$nextLevel}\n";
echo "Next Approver Role: " . ($flow[$nextLevel] ?? 'N/A') . "\n";

// Test the actual method
echo "\n=== TESTING ACTUAL METHOD ===\n";
$approvalService->initializeApprovalWorkflowAfterRevision($travelRequest->fresh());

$updatedRequest = $travelRequest->fresh();
echo "After workflow initialization:\n";
echo "- Status: {$updatedRequest->status}\n";
echo "- Current Approval Level: {$updatedRequest->current_approval_level}\n";
echo "- Current Approver Role: " . ($flow[$updatedRequest->current_approval_level] ?? 'N/A') . "\n"; 
