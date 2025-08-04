<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;

echo "=== ADDING TEST PARTICIPANTS ===\n\n";

$travelRequest = TravelRequest::find(22);
if ($travelRequest) {
    echo "Travel Request ID: {$travelRequest->id}\n";
    echo "Status: {$travelRequest->status}\n";
    
    // Get some staff users
    $staffUsers = User::where('role', 'staff')->limit(3)->get();
    
    if ($staffUsers->count() > 0) {
        echo "Adding participants:\n";
        foreach ($staffUsers as $user) {
            echo "- {$user->name} ({$user->role})\n";
        }
        
        // Attach participants
        $travelRequest->participants()->attach($staffUsers->pluck('id'));
        
        echo "\n✅ Participants added successfully!\n";
        echo "Total participants: {$travelRequest->participants()->count()}\n";
    } else {
        echo "No staff users found\n";
    }
} else {
    echo "Travel Request not found\n";
} 
