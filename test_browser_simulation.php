<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== SIMULASI BROWSER - TEST PARTICIPANT UPDATE ===\n";

// 1. Simulate initial state (what user sees in edit page)
echo "1. INITIAL STATE (Edit Page):\n";
$tr = TravelRequest::with('participants')->find(32);
echo "Current participants: " . $tr->participants->count() . "\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

// 2. Simulate user adding more participants (like in screenshot: SUHARYONO, THOYIB, RAMADHAN, SABAR)
echo "\n2. USER ADDS MORE PARTICIPANTS:\n";
$newParticipantIds = [20, 21, 15, 14]; // SUHARYONO (20), THOYIB (21), RAMADHAN (15), SABAR (14)
echo "User wants participants: " . implode(', ', $newParticipantIds) . "\n";

// 3. Simulate JavaScript window.selectedPeserta
echo "\n3. JAVASCRIPT window.selectedPeserta:\n";
$windowSelectedPeserta = $newParticipantIds;
echo "window.selectedPeserta = " . json_encode($windowSelectedPeserta) . "\n";

// 4. Simulate form submission data
echo "\n4. FORM SUBMISSION DATA:\n";
$formData = [
    '_token' => 'test_token',
    'participants' => $windowSelectedPeserta
];
echo "Form data to send: " . json_encode($formData) . "\n";

// 5. Test the submit method with this data
echo "\n5. TESTING SUBMIT METHOD:\n";

// Simulate the submit method logic
$participantsToSync = $formData['participants'];
echo "Participants to sync: " . json_encode($participantsToSync) . "\n";

// Update the database with new participants
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

// Verify the result
$tr->load('participants');
echo "\n6. RESULT AFTER SUBMIT:\n";
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "This simulates what should happen when user adds participants and submits.\n"; 