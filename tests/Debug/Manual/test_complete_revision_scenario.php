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

echo "=== TESTING COMPLETE REVISION SCENARIOS ===\n\n";

$approvalService = new ApprovalService();

// Test Scenario 1: PPK revises to Kasubbag, should go back to PPK
echo "=== SCENARIO 1: PPK REVISES TO KASUBBAG ===\n";

// Create a test travel request with both Sekretaris and PPK approved
$testRequest = TravelRequest::create([
    'user_id' => 1, // kasubbag
    'tempat_berangkat' => 'Cirebon',
    'tujuan' => 'Jakarta',
    'keperluan' => 'Test revision workflow',
    'tanggal_berangkat' => '2025-08-01',
    'tanggal_kembali' => '2025-08-03',
    'lama_perjalanan' => 3,
    'transportasi' => 'Pesawat',
    'status' => 'revision',
    'current_approval_level' => 0,
]);

// Add approvals: Sekretaris approved, PPK approved
Approval::create([
    'travel_request_id' => $testRequest->id,
    'approver_id' => 2, // sekretaris
    'level' => 1,
    'role' => 'sekretaris',
    'status' => 'approved',
    'approved_at' => now(),
]);

Approval::create([
    'travel_request_id' => $testRequest->id,
    'approver_id' => 3, // ppk
    'level' => 2,
    'role' => 'ppk',
    'status' => 'approved',
    'approved_at' => now(),
]);

echo "Created test travel request ID: {$testRequest->id}\n";
echo "Status: {$testRequest->status}\n";
echo "Current Approval Level: {$testRequest->current_approval_level}\n";

// Check approved levels
$approvedLevels = $testRequest->approvals()->where('status', 'approved')->pluck('level')->toArray();
echo "Approved Levels: " . json_encode($approvedLevels) . "\n";

// Test the workflow
$approvalService->initializeApprovalWorkflowAfterRevision($testRequest->fresh());

$updatedRequest = $testRequest->fresh();
echo "After workflow initialization:\n";
echo "- Status: {$updatedRequest->status}\n";
echo "- Current Approval Level: {$updatedRequest->current_approval_level}\n";

// Determine expected level
$flow = $approvalService->getApprovalFlowForSubmitter('kasubbag');
$expectedLevel = empty($approvedLevels) ? 1 : (max($approvedLevels) + 1);
if (!isset($flow[$expectedLevel])) {
    $expectedLevel = 1;
}

echo "Expected Level: {$expectedLevel}\n";
echo "Expected Approver: " . ($flow[$expectedLevel] ?? 'N/A') . "\n";

if ($updatedRequest->current_approval_level == $expectedLevel) {
    echo "✅ SUCCESS: Workflow correctly determined next level!\n";
} else {
    echo "❌ FAILED: Expected level {$expectedLevel}, got {$updatedRequest->current_approval_level}\n";
}

// Clean up
$testRequest->approvals()->delete();
$testRequest->delete();

echo "\n=== TEST COMPLETED ===\n"; 
