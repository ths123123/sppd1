<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TESTING JAVASCRIPT SIMULATION ===\n";

// Simulate what should happen in JavaScript
echo "SIMULATING JAVASCRIPT BEHAVIOR:\n";

// 1. User changes participants (removes some)
$originalParticipants = [13, 8, 22, 20, 21, 23]; // 6 participants
$userChangedParticipants = [20, 21]; // User keeps only 2

echo "Original participants: " . implode(', ', $originalParticipants) . "\n";
echo "User changed participants: " . implode(', ', $userChangedParticipants) . "\n";

// 2. JavaScript should update window.selectedPeserta
$windowSelectedPeserta = $userChangedParticipants;
echo "window.selectedPeserta: " . json_encode($windowSelectedPeserta) . "\n";

// 3. JavaScript should create hidden inputs
$hiddenInputs = [];
foreach ($windowSelectedPeserta as $participantId) {
    $hiddenInputs[] = [
        'name' => 'participants[]',
        'value' => $participantId
    ];
}

echo "Hidden inputs to create:\n";
foreach ($hiddenInputs as $index => $input) {
    echo "- Input " . ($index + 1) . ": name='" . $input['name'] . "', value='" . $input['value'] . "'\n";
}

// 4. Simulate form submission with this data
echo "\nSIMULATED FORM SUBMISSION DATA:\n";
$formData = [
    '_token' => 'test_token',
    'participants' => $windowSelectedPeserta
];

echo "Form data: " . json_encode($formData) . "\n";

// 5. Test if the submit method would work with this data
echo "\nTESTING SUBMIT METHOD WITH SIMULATED DATA:\n";

$tr = TravelRequest::with('participants')->find(32);
$participantService = app(\App\Services\ParticipantService::class);

// Simulate the submit method logic
$participantsToSync = $formData['participants'];
echo "Participants to sync: " . json_encode($participantsToSync) . "\n";

$participantService->syncParticipants($tr, $participantsToSync);

// Verify the result
$tr->load('participants');
echo "\nRESULT AFTER SYNC:\n";
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "If this works correctly, the form submission should work in the browser.\n"; 
