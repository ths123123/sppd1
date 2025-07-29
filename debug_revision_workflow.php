<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\User;
use App\Services\ApprovalService;

echo "=== DEBUGGING REVISION WORKFLOW ===\n\n";

$approvalService = new ApprovalService();

// Get the test travel request
$testRequest = TravelRequest::with(['approvals', 'user'])->find(22);
if (!$testRequest) {
    echo "❌ Test travel request not found\n";
    exit;
}

echo "Travel Request ID: {$testRequest->id}\n";
echo "Status: {$testRequest->status}\n";
echo "Current Approval Level: {$testRequest->current_approval_level}\n\n";

// Check all approvals
$allApprovals = $testRequest->approvals()->get();
echo "All approvals:\n";
foreach ($allApprovals as $approval) {
    echo "- ID: {$approval->id}, Level: {$approval->level}, Role: {$approval->role}, Status: {$approval->status}\n";
}

// Check approved levels
$approvedLevels = $testRequest->approvals()->where('status', 'approved')->pluck('level')->toArray();
echo "\nApproved Levels: " . json_encode($approvedLevels) . "\n";

// Get flow
$submitterRole = $testRequest->user->role;
$flow = $approvalService->getApprovalFlowForSubmitter($submitterRole);
echo "Submitter Role: {$submitterRole}\n";
echo "Approval Flow: " . json_encode($flow) . "\n";

// Manual calculation
if (empty($approvedLevels)) {
    $nextLevel = 1;
} else {
    $highestApprovedLevel = max($approvedLevels);
    $nextLevel = $highestApprovedLevel + 1;
    
    if (!isset($flow[$nextLevel])) {
        echo "Next level {$nextLevel} doesn't exist in flow, should mark as completed\n";
        $nextLevel = $highestApprovedLevel;
    }
}

echo "Calculated Next Level: {$nextLevel}\n";
echo "Next Approver Role: " . ($flow[$nextLevel] ?? 'N/A') . "\n";

// Test the method step by step
echo "\n=== TESTING METHOD STEP BY STEP ===\n";

// Check if this is a resubmission after revision
$hasPreviousApprovals = $testRequest->approvals()->where('status', 'approved')->exists();
echo "Has previous approvals: " . ($hasPreviousApprovals ? 'YES' : 'NO') . "\n";

if ($hasPreviousApprovals) {
    echo "Using initializeApprovalWorkflowAfterRevision\n";
    $approvalService->initializeApprovalWorkflowAfterRevision($testRequest->fresh());
} else {
    echo "Using initializeApprovalWorkflow\n";
    $approvalService->initializeApprovalWorkflow($testRequest->fresh());
}

$updatedRequest = $testRequest->fresh();
echo "\nAfter workflow initialization:\n";
echo "- Status: {$updatedRequest->status}\n";
echo "- Current Approval Level: {$updatedRequest->current_approval_level}\n"; 