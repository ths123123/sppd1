<?php
/**
 * Test Runner - PHP Script untuk Testing Database dan API
 * Script ini akan menguji semua komponen yang diperlukan untuk aktivitas dashboard
 */

echo "🎯 STARTING COMPREHENSIVE ACTIVITY TEST\n";
echo "=====================================\n\n";

// Test 1: Database Connection
echo "=== TEST 1: DATABASE CONNECTION ===\n";
try {
    require_once '../vendor/autoload.php';

    // Test Laravel connection
    $app = require_once '../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "✅ Laravel framework loaded successfully\n";

    // Test database connection
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "✅ Database connection successful\n";

        // Check if tables exist
        $tables = ['users', 'travel_requests', 'activity_logs', 'approvals'];
        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                echo "✅ Table '{$table}' exists\n";
            } else {
                echo "❌ Table '{$table}' missing\n";
            }
        }

    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "\n";
}

echo "\n=== TEST 2: DATA VERIFICATION ===\n";

// Test if there's data in tables
try {
    // Check users
    $userCount = \Illuminate\Support\Facades\DB::table('users')->count();
    echo "📊 Users in database: {$userCount}\n";

    // Check travel requests
    $travelCount = \Illuminate\Support\Facades\DB::table('travel_requests')->count();
    echo "📊 Travel requests in database: {$travelCount}\n";

    // Check activity logs
    $activityCount = \Illuminate\Support\Facades\DB::table('activity_logs')->count();
    echo "📊 Activity logs in database: {$activityCount}\n";

    // Check approvals
    $approvalCount = \Illuminate\Support\Facades\DB::table('approvals')->count();
    echo "📊 Approvals in database: {$approvalCount}\n";

} catch (Exception $e) {
    echo "❌ Data verification failed: " . $e->getMessage() . "\n";
}

echo "\n=== TEST 3: SAMPLE DATA GENERATION ===\n";

// Generate sample data if needed
try {
    if ($travelCount == 0) {
        echo "🔧 No travel requests found, generating sample data...\n";

        // Create sample travel request
        $sampleRequest = [
            'user_id' => 1,
            'kode_sppd' => 'SPPD-TEST-001',
            'tujuan' => 'Jakarta',
            'keperluan' => 'Test perjalanan dinas',
            'tanggal_berangkat' => now(),
            'tanggal_kembali' => now()->addDays(2),
            'status' => 'submitted',
            'created_at' => now(),
            'updated_at' => now()
        ];

        \Illuminate\Support\Facades\DB::table('travel_requests')->insert($sampleRequest);
        echo "✅ Sample travel request created\n";

        // Create sample activity log
        $sampleActivity = [
            'user_id' => 1,
            'action' => 'SPPD baru diajukan',
            'description' => 'SPPD TEST-001 diajukan untuk perjalanan dinas ke Jakarta',
            'model_type' => 'App\\Models\\TravelRequest',
            'model_id' => \Illuminate\Support\Facades\DB::getPdo()->lastInsertId(),
            'details' => json_encode(['kode_sppd' => 'SPPD-TEST-001', 'tujuan' => 'Jakarta']),
            'created_at' => now(),
            'updated_at' => now()
        ];

        \Illuminate\Support\Facades\DB::table('activity_logs')->insert($sampleActivity);
        echo "✅ Sample activity log created\n";

    } else {
        echo "✅ Travel requests already exist\n";
    }

} catch (Exception $e) {
    echo "❌ Sample data generation failed: " . $e->getMessage() . "\n";
}

echo "\n=== TEST 4: API ENDPOINT TEST ===\n";

// Test API endpoints
try {
    // Test recent activities endpoint
    echo "🔍 Testing /dashboard/recent-activities endpoint...\n";

    // Create a simple HTTP request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/dashboard/recent-activities');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        echo "✅ API endpoint accessible (HTTP 200)\n";

        $data = json_decode($response, true);
        if ($data && isset($data['success'])) {
            echo "✅ API response format correct\n";
            if ($data['success'] && isset($data['data'])) {
                echo "✅ API returned data: " . count($data['data']) . " activities\n";
            } else {
                echo "⚠️ API returned success: false\n";
            }
        } else {
            echo "❌ API response format incorrect\n";
        }
    } else {
        echo "❌ API endpoint failed (HTTP {$httpCode})\n";
    }

} catch (Exception $e) {
    echo "❌ API test failed: " . $e->getMessage() . "\n";
}

echo "\n=== TEST 5: FINAL VERIFICATION ===\n";

// Final data count
try {
    $finalTravelCount = \Illuminate\Support\Facades\DB::table('travel_requests')->count();
    $finalActivityCount = \Illuminate\Support\Facades\DB::table('activity_logs')->count();

    echo "📊 Final counts:\n";
    echo "   - Travel requests: {$finalTravelCount}\n";
    echo "   - Activity logs: {$finalActivityCount}\n";

    if ($finalTravelCount > 0 && $finalActivityCount > 0) {
        echo "✅ SUCCESS: Data available for dashboard!\n";
        echo "🎉 The dashboard should now display activities!\n";
    } else {
        echo "❌ FAILED: No data available for dashboard\n";
    }

} catch (Exception $e) {
    echo "❌ Final verification failed: " . $e->getMessage() . "\n";
}

echo "\n=====================================\n";
echo "🎯 TEST COMPLETED\n";
echo "=====================================\n";
echo "💡 Next steps:\n";
echo "   1. Refresh dashboard in browser\n";
echo "   2. Check browser console for JavaScript tests\n";
echo "   3. Look for activities in the dashboard\n";
echo "   4. If still no activities, check console errors\n";
echo "\n";
?>
