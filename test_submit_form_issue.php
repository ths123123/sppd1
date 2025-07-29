<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TEST SUBMIT FORM ISSUE ===\n";

// 1. Simulate the current state (what user sees in edit page)
echo "1. CURRENT STATE (Edit Page):\n";
$tr = TravelRequest::with('participants')->find(32);
echo "Current participants: " . $tr->participants->count() . "\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

// 2. Simulate user making changes (reducing participants)
echo "\n2. USER MAKES CHANGES (Reduces to 1 participant):\n";
$userSelectedPeserta = [20]; // Only SUHARYONO
echo "User selected participants: " . implode(', ', $userSelectedPeserta) . "\n";

// 3. Simulate JavaScript window.selectedPeserta
echo "\n3. JAVASCRIPT window.selectedPeserta:\n";
$windowSelectedPeserta = $userSelectedPeserta;
echo "window.selectedPeserta = " . json_encode($windowSelectedPeserta) . "\n";

// 4. Simulate form submission data (what actually gets sent)
echo "\n4. FORM SUBMISSION DATA (What actually gets sent):\n";
$formData = [
    '_token' => 'test_token',
    'participants' => null  // This is the problem - JavaScript not sending data
];
echo "Form data sent: " . json_encode($formData) . "\n";

// 5. Simulate what the controller receives
echo "\n5. CONTROLLER RECEIVES:\n";
$participantsFromRequest = $formData['participants'];
echo "participants_from_request: " . ($participantsFromRequest ? json_encode($participantsFromRequest) : 'null') . "\n";

// 6. Simulate the fallback logic
echo "\n6. FALLBACK LOGIC:\n";
if (empty($participantsFromRequest)) {
    $participantsToSync = $tr->participants()->pluck('user_id')->toArray();
    echo "No participants in request, using existing data: " . json_encode($participantsToSync) . "\n";
} else {
    $participantsToSync = $participantsFromRequest;
    echo "Using participants from request: " . json_encode($participantsToSync) . "\n";
}

// 7. Show the problem
echo "\n7. THE PROBLEM:\n";
echo "User wants: " . json_encode($userSelectedPeserta) . "\n";
echo "JavaScript has: " . json_encode($windowSelectedPeserta) . "\n";
echo "Form sends: " . json_encode($formData['participants']) . "\n";
echo "Controller uses: " . json_encode($participantsToSync) . "\n";

echo "\n=== ROOT CAUSE ===\n";
echo "JavaScript window.selectedPeserta is correct: " . json_encode($windowSelectedPeserta) . "\n";
echo "But form submission sends null/empty data\n";
echo "This means the JavaScript is not properly updating the form hidden inputs\n";

echo "\n=== SOLUTION ===\n";
echo "Need to ensure JavaScript properly updates the submit form hidden inputs\n";
echo "The issue is likely in the #submit-form event handler\n";

echo "\n=== TEST COMPLETE ===\n"; 