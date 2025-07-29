<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use App\Services\ApprovalService;

echo "=== CHECKING APPROVAL QUEUE ===\n\n";

$approvalService = new ApprovalService();

// Check for Sekretaris
$sekretaris = User::where('role', 'sekretaris')->first();
if ($sekretaris) {
    echo "=== SEKRETARIS APPROVAL QUEUE ===\n";
    $pendingForSekretaris = $approvalService->getPendingApprovalsForRole('sekretaris');
    echo "Pending approvals for Sekretaris: {$pendingForSekretaris->count()}\n";
    
    foreach ($pendingForSekretaris as $request) {
        echo "- ID: {$request->id}, Status: {$request->status}, Level: {$request->current_approval_level}\n";
    }
    echo "\n";
}

// Check for PPK
$ppk = User::where('role', 'ppk')->first();
if ($ppk) {
    echo "=== PPK APPROVAL QUEUE ===\n";
    $pendingForPPK = $approvalService->getPendingApprovalsForRole('ppk');
    echo "Pending approvals for PPK: {$pendingForPPK->count()}\n";
    
    foreach ($pendingForPPK as $request) {
        echo "- ID: {$request->id}, Status: {$request->status}, Level: {$request->current_approval_level}\n";
    }
    echo "\n";
}

// Check all in_review requests
echo "=== ALL IN_REVIEW REQUESTS ===\n";
$inReviewRequests = TravelRequest::where('status', 'in_review')->get();
echo "Total in_review requests: {$inReviewRequests->count()}\n";

foreach ($inReviewRequests as $request) {
    echo "- ID: {$request->id}, Level: {$request->current_approval_level}, Current Approver: {$request->current_approver_role}\n";
} 