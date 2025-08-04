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

echo "=== TESTING REALISTIC REVISION SCENARIOS ===\n\n";

$approvalService = new ApprovalService();

echo "=== SCENARIO 1: SEKRETARIS REVISES TO KASUBBAG ===\n";
echo "Expected: When Kasubbag resubmits, should go to Sekretaris (level 1)\n\n";

// Simulate: Sekretaris revises to Kasubbag
$travelRequest = TravelRequest::find(22);
$travelRequest->update(['status' => 'revision', 'current_approval_level' => 1]); // Level 1 = Sekretaris

// Clear PPK approval (since it was revision_minor)
Approval::where('travel_request_id', 22)->where('role', 'ppk')->delete();

echo "Before resubmission:\n";
echo "- Status: {$travelRequest->status}\n";
echo "- Current Level: {$travelRequest->current_approval_level}\n";

// Check approved levels
$approvedLevels = $travelRequest->approvals()->where('status', 'approved')->pluck('level')->toArray();
echo "- Approved Levels: " . json_encode($approvedLevels) . "\n";

// Test resubmission
$approvalService->initializeApprovalWorkflowAfterRevision($travelRequest->fresh());

$updatedRequest = $travelRequest->fresh();
echo "\nAfter resubmission:\n";
echo "- Status: {$updatedRequest->status}\n";
echo "- Current Level: {$updatedRequest->current_approval_level}\n";

// According to new logic: should go back to Sekretaris (level 1)
$expectedLevel = 1;
$flow = $approvalService->getApprovalFlowForSubmitter('kasubbag');

echo "- Expected Level: {$expectedLevel}\n";
echo "- Expected Approver: " . ($flow[$expectedLevel] ?? 'N/A') . "\n";

if ($updatedRequest->current_approval_level == $expectedLevel) {
    echo "✅ SUCCESS: Correctly goes to Sekretaris!\n";
} else {
    echo "❌ FAILED: Expected level {$expectedLevel}, got {$updatedRequest->current_approval_level}\n";
}

echo "\n=== SCENARIO 2: PPK REVISES TO KASUBBAG ===\n";
echo "Expected: When Kasubbag resubmits, should go to PPK (level 2)\n\n";

// Simulate: PPK revises to Kasubbag (both Sekretaris and PPK approved)
$travelRequest->update(['status' => 'revision', 'current_approval_level' => 2]); // Level 2 = PPK

// Add PPK approval
Approval::create([
    'travel_request_id' => 22,
    'approver_id' => 3, // ppk
    'level' => 2,
    'role' => 'ppk',
    'status' => 'approved',
    'approved_at' => now(),
]);

echo "Before resubmission:\n";
echo "- Status: {$travelRequest->status}\n";
echo "- Current Level: {$travelRequest->current_approval_level}\n";

// Check approved levels
$approvedLevels = $travelRequest->approvals()->where('status', 'approved')->pluck('level')->toArray();
echo "- Approved Levels: " . json_encode($approvedLevels) . "\n";

// Test resubmission
$approvalService->initializeApprovalWorkflowAfterRevision($travelRequest->fresh());

$updatedRequest = $travelRequest->fresh();
echo "\nAfter resubmission:\n";
echo "- Status: {$updatedRequest->status}\n";
echo "- Current Level: {$updatedRequest->current_approval_level}\n";

// According to new logic: should go back to PPK (level 2)
$expectedLevel = 2;

echo "- Expected Level: {$expectedLevel}\n";
echo "- Expected Approver: " . ($flow[$expectedLevel] ?? 'N/A') . "\n";

if ($updatedRequest->current_approval_level == $expectedLevel) {
    echo "✅ SUCCESS: Correctly goes to PPK!\n";
} else {
    echo "❌ FAILED: Expected level {$expectedLevel}, got {$updatedRequest->current_approval_level}\n";
}

echo "\n=== TEST COMPLETED ===\n"; 
