<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TEST DETAIL PAGE UPDATE ===\n";

// 1. Check current data in database
echo "1. CURRENT DATA IN DATABASE:\n";
$tr = TravelRequest::with('participants')->find(32);
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

// 2. Simulate user changes (reduce participants to 2)
echo "\n2. SIMULATING USER CHANGES (REDUCE TO 2 PARTICIPANTS):\n";
$newParticipantIds = [20, 21]; // Only SUHARYONO and THOYIB
echo "New participant IDs: " . implode(', ', $newParticipantIds) . "\n";

// 3. Update database
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

// 4. Verify database update
echo "\n3. VERIFY DATABASE UPDATE:\n";
$tr->load('participants');
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

// 5. Test the show method logic (simulate what happens in controller)
echo "\n4. TESTING SHOW METHOD LOGIC:\n";
$travelRequest = TravelRequest::with(['user', 'participants'])->findOrFail(32);
$travelRequest->load('participants');

echo "Show method participants count: " . $travelRequest->participants->count() . "\n";
echo "Show method participants:\n";
foreach ($travelRequest->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

// 6. Test view data
echo "\n5. TESTING VIEW DATA:\n";
$peserta = $travelRequest->participants ?? [];
echo "View peserta count: " . count($peserta) . "\n";
echo "View peserta:\n";
foreach ($peserta as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "If all counts match, the detail page should show updated data.\n";
echo "If not, there might be a caching issue.\n"; 
