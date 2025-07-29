<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\User;
use App\Services\ApprovalService;

echo "=== TESTING CORRECT REVISION LOGIC ===\n\n";

$approvalService = new ApprovalService();

echo "=== SCENARIO 1: SEKRETARIS REVISES (LEVEL 1) ===\n";
echo "Expected: When Kasubbag resubmits, should go to Sekretaris (level 1)\n\n";

// Get travel request 22
$travelRequest = TravelRequest::find(22);

// Clear all approvals first
$travelRequest->approvals()->delete();

// Set status to revision at level 1 (Sekretaris level)
$travelRequest->update(['status' => 'revision', 'current_approval_level' => 1]);

echo "Before resubmission:\n";
echo "- Status: {$travelRequest->status}\n";
echo "- Current Level: {$travelRequest->current_approval_level}\n";

// Test resubmission
$approvalService->initializeApprovalWorkflowAfterRevision($travelRequest->fresh());

$updatedRequest = $travelRequest->fresh();
echo "\nAfter resubmission:\n";
echo "- Status: {$updatedRequest->status}\n";
echo "- Current Level: {$updatedRequest->current_approval_level}\n";

$flow = $approvalService->getApprovalFlowForSubmitter('kasubbag');
$expectedLevel = 1; // Should go back to level 1 (Sekretaris)

echo "- Expected Level: {$expectedLevel}\n";
echo "- Expected Approver: " . ($flow[$expectedLevel] ?? 'N/A') . "\n";

if ($updatedRequest->current_approval_level == $expectedLevel) {
    echo "✅ SUCCESS: Correctly goes to Sekretaris!\n";
} else {
    echo "❌ FAILED: Expected level {$expectedLevel}, got {$updatedRequest->current_approval_level}\n";
}

echo "\n=== SCENARIO 2: PPK REVISES (LEVEL 2) ===\n";
echo "Expected: When Kasubbag resubmits, should go to PPK (level 2)\n\n";

// Set status to revision at level 2 (PPK level)
$travelRequest->update(['status' => 'revision', 'current_approval_level' => 2]);

echo "Before resubmission:\n";
echo "- Status: {$travelRequest->status}\n";
echo "- Current Level: {$travelRequest->current_approval_level}\n";

// Test resubmission
$approvalService->initializeApprovalWorkflowAfterRevision($travelRequest->fresh());

$updatedRequest = $travelRequest->fresh();
echo "\nAfter resubmission:\n";
echo "- Status: {$updatedRequest->status}\n";
echo "- Current Level: {$updatedRequest->current_approval_level}\n";

$expectedLevel = 2; // Should go back to level 2 (PPK)

echo "- Expected Level: {$expectedLevel}\n";
echo "- Expected Approver: " . ($flow[$expectedLevel] ?? 'N/A') . "\n";

if ($updatedRequest->current_approval_level == $expectedLevel) {
    echo "✅ SUCCESS: Correctly goes to PPK!\n";
} else {
    echo "❌ FAILED: Expected level {$expectedLevel}, got {$updatedRequest->current_approval_level}\n";
}

echo "\n=== TEST COMPLETED ===\n"; 