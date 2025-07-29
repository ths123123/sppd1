<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;

echo "=== TRAVEL REQUEST 32 DATA ===\n";

$tr = TravelRequest::with('participants')->find(32);

if (!$tr) {
    echo "Travel Request 32 not found!\n";
    exit;
}

echo "ID: " . $tr->id . "\n";
echo "Status: " . $tr->status . "\n";
echo "Updated At: " . $tr->updated_at . "\n";
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";

foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

echo "\n=== TRAVEL REQUEST PARTICIPANTS TABLE ===\n";
$participants = \DB::table('travel_request_participants')
    ->where('travel_request_id', 32)
    ->get();

echo "Raw participants data:\n";
foreach ($participants as $p) {
    echo "- User ID: " . $p->user_id . "\n";
}

echo "\n=== RECENT LOGS ===\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $recentLines = array_slice($lines, -20);
    foreach ($recentLines as $line) {
        if (strpos($line, '32') !== false || strpos($line, 'participant') !== false || strpos($line, 'sync') !== false) {
            echo trim($line) . "\n";
        }
    }
} 