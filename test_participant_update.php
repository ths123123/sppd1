<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TESTING PARTICIPANT UPDATE FOR REQUEST 32 ===\n";

// Get current data
$tr = TravelRequest::with('participants')->find(32);
echo "BEFORE UPDATE:\n";
echo "ID: " . $tr->id . "\n";
echo "Status: " . $tr->status . "\n";
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

// Simulate user changes - remove some participants (keep only 2)
echo "\n=== SIMULATING USER CHANGES ===\n";
$newParticipantIds = [20, 21]; // Keep only SUHARYONO and THOYIB
echo "New participant IDs: " . implode(', ', $newParticipantIds) . "\n";

// Update participants in database
DB::table('travel_request_participants')
    ->where('travel_request_id', 32)
    ->delete();

foreach ($newParticipantIds as $userId) {
    DB::table('travel_request_participants')->insert([
        'travel_request_id' => 32,
        'user_id' => $userId,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

// Verify the change
$tr->load('participants');
echo "\nAFTER SIMULATED CHANGES:\n";
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "Now you can test the 'Ajukan Ulang' functionality in the browser.\n";
echo "The system should now have only 2 participants instead of 6.\n"; 