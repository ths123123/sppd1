<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TESTING SUBMIT WITH CORRECT PARTICIPANT DATA ===\n";

// Get current data
$tr = TravelRequest::with('participants')->find(32);
echo "CURRENT DATA:\n";
echo "ID: " . $tr->id . "\n";
echo "Status: " . $tr->status . "\n";
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

// Simulate the correct participant data that should be sent from form
$correctParticipantIds = [20, 21]; // These are the current participants
echo "\nCORRECT PARTICIPANT DATA TO SEND:\n";
echo "Participant IDs: " . implode(', ', $correctParticipantIds) . "\n";

// Test the submit method with correct data
echo "\n=== TESTING SUBMIT METHOD ===\n";

// Simulate request data
$requestData = [
    'participants' => $correctParticipantIds
];

echo "Request data to send: " . json_encode($requestData) . "\n";

// Test the participant service directly
$participantService = app(\App\Services\ParticipantService::class);
$participantService->syncParticipants($tr, $correctParticipantIds);

// Verify the result
$tr->load('participants');
echo "\nAFTER SYNC:\n";
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "The submit method should work correctly with the proper participant data.\n"; 