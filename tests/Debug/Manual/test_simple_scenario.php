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

echo "=== TESTING SIMPLE REVISION SCENARIOS ===\n\n";

$approvalService = new ApprovalService();

echo "=== SCENARIO: SEKRETARIS REVISES TO KASUBBAG ===\n";

// Get travel request 22
$travelRequest = TravelRequest::find(22);

// Clear all approvals first
$travelRequest->approvals()->delete();

// Add only Sekretaris approval (level 1)
Approval::create([
    'travel_request_id' => 22,
    'approver_id' => 2, // sekretaris
    'level' => 1,
    'role' => 'sekretaris',
    'status' => 'approved',
    'approved_at' => now(),
]);

// Set status to revision
$travelRequest->update(['status' => 'revision', 'current_approval_level' => 0]);

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

$flow = $approvalService->getApprovalFlowForSubmitter('kasubbag');
$expectedLevel = empty($approvedLevels) ? 1 : (max($approvedLevels) + 1);
if (!isset($flow[$expectedLevel])) {
    $expectedLevel = 1;
}

echo "- Expected Level: {$expectedLevel}\n";
echo "- Expected Approver: " . ($flow[$expectedLevel] ?? 'N/A') . "\n";

if ($updatedRequest->current_approval_level == $expectedLevel) {
    echo "✅ SUCCESS: Correctly goes to next approver!\n";
} else {
    echo "❌ FAILED: Expected level {$expectedLevel}, got {$updatedRequest->current_approval_level}\n";
}

echo "\n=== TEST COMPLETED ===\n"; 
