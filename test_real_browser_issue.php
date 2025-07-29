<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== SIMULASI MASALAH BROWSER YANG SEBENARNYA ===\n";

// Simulate the actual problem: form submit sends empty participants data
echo "1. SIMULASI FORM SUBMIT DENGAN DATA KOSONG:\n";
$emptyFormData = [
    '_token' => 'test_token',
    'participants' => null  // This is what's actually happening
];
echo "Form data sent: " . json_encode($emptyFormData) . "\n";

// 2. Simulate the submit method receiving empty data
echo "\n2. SUBMIT METHOD MENERIMA DATA KOSONG:\n";
$participantsFromRequest = $emptyFormData['participants'];
echo "participants_from_request: " . ($participantsFromRequest ? json_encode($participantsFromRequest) : 'null') . "\n";

// 3. Simulate the fallback logic
echo "\n3. FALLBACK LOGIC:\n";
if (empty($participantsFromRequest)) {
    $tr = TravelRequest::with('participants')->find(32);
    $participantsToSync = $tr->participants()->pluck('user_id')->toArray();
    echo "No participants in request, using existing data: " . json_encode($participantsToSync) . "\n";
} else {
    $participantsToSync = $participantsFromRequest;
    echo "Using participants from request: " . json_encode($participantsToSync) . "\n";
}

// 4. Show what happens in the database
echo "\n4. HASIL DI DATABASE:\n";
$tr = TravelRequest::with('participants')->find(32);
echo "Participants Count: " . $tr->participants->count() . "\n";
echo "Participants:\n";
foreach ($tr->participants as $p) {
    echo "- " . $p->name . " (ID: " . $p->id . ")\n";
}

echo "\n=== ANALISIS MASALAH ===\n";
echo "Masalah: Form submit mengirim data kosong, sehingga method submit menggunakan data lama\n";
echo "Solusi: JavaScript harus mengirim data peserta yang benar\n";

echo "\n=== TEST COMPLETE ===\n"; 