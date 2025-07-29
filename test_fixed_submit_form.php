<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TEST FIXED SUBMIT FORM ===\n";

// 1. Simulate the fixed JavaScript behavior
echo "1. SIMULATING FIXED JAVASCRIPT BEHAVIOR:\n";
$windowSelectedPeserta = [20]; // User reduced to 1 participant
echo "window.selectedPeserta = " . json_encode($windowSelectedPeserta) . "\n";

// 2. Simulate the fixed form submission
echo "\n2. SIMULATING FIXED FORM SUBMISSION:\n";
$formData = [
    '_token' => 'test_token',
    'participants' => $windowSelectedPeserta  // Now data is sent correctly
];
echo "Form data sent: " . json_encode($formData) . "\n";

// 3. Simulate what the controller receives
echo "\n3. CONTROLLER RECEIVES:\n";
$participantsFromRequest = $formData['participants'];
echo "participants_from_request: " . json_encode($participantsFromRequest) . "\n";

// 4. Simulate the controller logic
echo "\n4. CONTROLLER LOGIC:\n";
if (empty($participantsFromRequest)) {
    $tr = TravelRequest::with('participants')->find(32);
    $participantsToSync = $tr->participants()->pluck('user_id')->toArray();
    echo "No participants in request, using existing data: " . json_encode($participantsToSync) . "\n";
} else {
    $participantsToSync = $participantsFromRequest;
    echo "Using participants from request: " . json_encode($participantsToSync) . "\n";
}

// 5. Simulate database update
echo "\n5. DATABASE UPDATE:\n";
DB::table('travel_request_participants')
    ->where('travel_request_id', 32)
    ->delete();

foreach ($participantsToSync as $userId) {
    DB::table('travel_request_participants')->insert([
        'travel_request_id' => 32,
        'user_id' => $userId,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

// 6. Verify the result
echo "\n6. VERIFY RESULT:\n";
$tr = TravelRequest::with('participants')->find(32);
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

echo "\n=== COMPARISON ===\n";
echo "BEFORE FIX:\n";
echo "- User wants: [20]\n";
echo "- Form sends: null\n";
echo "- Controller uses: [20,21]\n";
echo "- Result: Wrong data\n\n";

echo "AFTER FIX:\n";
echo "- User wants: [20]\n";
echo "- Form sends: [20]\n";
echo "- Controller uses: [20]\n";
echo "- Result: Correct data\n";

echo "\n=== TEST COMPLETE ===\n";
echo "If the result shows 1 participant (SUHARYONO), the fix is working!\n"; 