<?php

require_once 'vendor/autoload.php';

use App\Models\TravelRequest;
use App\Models\User;
use App\Services\ParticipantService;

// Test participant sync functionality
echo "=== TEST PESERTA SYNC ===\n";

// Get a travel request
$travelRequest = TravelRequest::with('participants')->find(32);
if (!$travelRequest) {
    echo "Travel Request ID 32 not found!\n";
    exit;
}

echo "Travel Request ID: " . $travelRequest->id . "\n";
echo "Current participants: " . $travelRequest->participants->count() . "\n";

foreach ($travelRequest->participants as $participant) {
    echo "- " . $participant->name . " (ID: " . $participant->id . ")\n";
}

// Test participant service
$participantService = new ParticipantService();

// Simulate removing some participants
$currentParticipantIds = $travelRequest->participants->pluck('id')->toArray();
echo "\nCurrent participant IDs: " . implode(', ', $currentParticipantIds) . "\n";

// Remove first participant for testing
if (!empty($currentParticipantIds)) {
    $remainingParticipants = array_slice($currentParticipantIds, 1);
    echo "Remaining participants after removal: " . implode(', ', $remainingParticipants) . "\n";
    
    // Test sync
    $participantService->syncParticipants($travelRequest, $remainingParticipants);
    
    // Refresh and check
    $travelRequest->refresh();
    echo "\nAfter sync - participants count: " . $travelRequest->participants->count() . "\n";
    
    foreach ($travelRequest->participants as $participant) {
        echo "- " . $participant->name . " (ID: " . $participant->id . ")\n";
    }
}

echo "\n=== TEST COMPLETED ===\n"; 