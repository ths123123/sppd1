<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TravelRequest;
use App\Models\User;
use App\Models\Approval;

echo "=== SYSTEM STATUS TEST ===\n\n";

// Test database connection
try {
    $userCount = User::count();
    echo "✅ Database connection: OK\n";
    echo "✅ Total users: {$userCount}\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test travel requests
try {
    $travelRequestCount = TravelRequest::count();
    echo "✅ Travel requests: {$travelRequestCount}\n";
} catch (Exception $e) {
    echo "❌ Travel requests error: " . $e->getMessage() . "\n";
}

// Test approvals
try {
    $approvalCount = Approval::count();
    echo "✅ Approvals: {$approvalCount}\n";
} catch (Exception $e) {
    echo "❌ Approvals error: " . $e->getMessage() . "\n";
}

// Test roles
try {
    $roles = User::select('role')->distinct()->pluck('role')->toArray();
    echo "✅ Available roles: " . implode(', ', $roles) . "\n";
} catch (Exception $e) {
    echo "❌ Roles error: " . $e->getMessage() . "\n";
}

// Test status distribution
try {
    $statuses = TravelRequest::select('status')->distinct()->pluck('status')->toArray();
    echo "✅ Available statuses: " . implode(', ', $statuses) . "\n";
} catch (Exception $e) {
    echo "❌ Statuses error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETED ===\n"; 