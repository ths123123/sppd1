<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== TEST BROWSER CACHE ISSUE ===\n";

// 1. Check if there are any cached queries
echo "1. CHECKING FOR CACHED QUERIES:\n";
$tr1 = TravelRequest::with('participants')->find(32);
echo "First query participants count: " . $tr1->participants->count() . "\n";

$tr2 = TravelRequest::with('participants')->find(32);
echo "Second query participants count: " . $tr2->participants->count() . "\n";

// 2. Test fresh query
echo "\n2. TESTING FRESH QUERY:\n";
$tr3 = TravelRequest::with('participants')->find(32);
$tr3->load('participants');
echo "Fresh query participants count: " . $tr3->participants->count() . "\n";

// 3. Test direct database query
echo "\n3. TESTING DIRECT DATABASE QUERY:\n";
$participants = DB::table('travel_request_participants')
    ->where('travel_request_id', 32)
    ->get();
echo "Direct DB query participants count: " . $participants->count() . "\n";

// 4. Test model relationship
echo "\n4. TESTING MODEL RELATIONSHIP:\n";
$tr4 = TravelRequest::find(32);
$participants = $tr4->participants()->get();
echo "Model relationship participants count: " . $participants->count() . "\n";

// 5. Test with fresh model
echo "\n5. TESTING WITH FRESH MODEL:\n";
$tr5 = TravelRequest::find(32);
$tr5->refresh();
$participants = $tr5->participants()->get();
echo "Fresh model participants count: " . $participants->count() . "\n";

echo "\n=== ANALYSIS ===\n";
echo "If all counts are the same, the issue might be:\n";
echo "1. Browser cache (Ctrl+F5 to hard refresh)\n";
echo "2. Server cache (already cleared)\n";
echo "3. Database transaction not committed\n";
echo "4. JavaScript not sending updated data\n";

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Hard refresh browser (Ctrl+F5)\n";
echo "2. Clear browser cache\n";
echo "3. Check browser console for JavaScript errors\n";
echo "4. Verify form submission in browser network tab\n";

echo "\n=== TEST COMPLETE ===\n"; 