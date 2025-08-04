<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;

echo "=== CHECKING PARTICIPANTS ===\n\n";

$travelRequest = TravelRequest::with('participants')->find(22);
if ($travelRequest) {
    echo "Travel Request ID: {$travelRequest->id}\n";
    echo "Status: {$travelRequest->status}\n";
    echo "Participants count: {$travelRequest->participants->count()}\n\n";
    
    if ($travelRequest->participants->count() > 0) {
        echo "Participants:\n";
        foreach ($travelRequest->participants as $participant) {
            echo "- ID: {$participant->id}, Name: {$participant->name}, Role: {$participant->role}\n";
        }
    } else {
        echo "No participants found\n";
    }
} else {
    echo "Travel Request not found\n";
} 