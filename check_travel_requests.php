<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;

echo "=== TRAVEL REQUESTS STATUS ===\n";
echo "Total: " . TravelRequest::count() . "\n\n";

$travelRequests = TravelRequest::all();
foreach ($travelRequests as $tr) {
    echo "ID: {$tr->id}\n";
    echo "Status: {$tr->status}\n";
    echo "Kode SPPD: {$tr->kode_sppd}\n";
    echo "Current Approval Level: {$tr->current_approval_level}\n";
    echo "Submitted At: {$tr->submitted_at}\n";
    echo "---\n";
} 