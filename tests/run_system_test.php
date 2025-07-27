<?php
/**
 * COMPREHENSIVE SYSTEM TEST
 * SISTEM SPPD KPU KABUPATEN CIREBON
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\Document;
use Illuminate\Support\Facades\Route;

echo "🧪 COMPREHENSIVE SYSTEM TEST\n";
echo "============================\n\n";

$tests = [];
$passed = 0;
$failed = 0;

function runTest($name, $callback) {
    global $tests, $passed, $failed;
    
    echo "Testing: {$name}... ";
    
    try {
        $result = $callback();
        if ($result) {
            echo "✅ PASSED\n";
            $passed++;
        } else {
            echo "❌ FAILED\n";
            $failed++;
        }
        $tests[] = ['name' => $name, 'status' => $result ? 'PASSED' : 'FAILED'];
    } catch (Exception $e) {
        echo "❌ FAILED - " . $e->getMessage() . "\n";
        $failed++;
        $tests[] = ['name' => $name, 'status' => 'FAILED', 'error' => $e->getMessage()];
    }
}

try {
    echo "1. 🔐 AUTHENTICATION & USER TESTING\n";
    echo "==================================\n";
    
    // Test 1: Admin user exists
    runTest("Admin user exists", function() {
        $admin = User::where('role', 'admin')->first();
        return $admin && $admin->email === 'admin@kpu.go.id';
    });
    
    // Test 2: Admin user is active
    runTest("Admin user is active", function() {
        $admin = User::where('role', 'admin')->first();
        return $admin && $admin->is_active;
    });
    
    // Test 3: Admin password is hashed
    runTest("Admin password is hashed", function() {
        $admin = User::where('role', 'admin')->first();
        return $admin && strlen($admin->password) > 50;
    });
    
    // Test 4: Single admin user only
    runTest("Single admin user only", function() {
        return User::where('role', 'admin')->count() === 1;
    });
    
    echo "\n2. 🗄️ DATABASE & MODEL TESTING\n";
    echo "=============================\n";
    
    // Test 5: Database connection
    runTest("Database connection", function() {
        return User::count() >= 0;
    });
    
    // Test 6: Models exist
    runTest("User model exists", function() {
        return class_exists('App\Models\User');
    });
    
    runTest("TravelRequest model exists", function() {
        return class_exists('App\Models\TravelRequest');
    });
    
    runTest("Approval model exists", function() {
        return class_exists('App\Models\Approval');
    });
    
    runTest("Document model exists", function() {
        return class_exists('App\Models\Document');
    });
    
    echo "\n3. 🛣️ ROUTE TESTING\n";
    echo "==================\n";
    
    // Test 7: Login route exists
    runTest("Login route exists", function() {
        return Route::has('login');
    });
    
    // Test 8: Dashboard route exists
    runTest("Dashboard route exists", function() {
        return Route::has('dashboard');
    });
    
    // Test 9: Travel requests route exists
    runTest("Travel requests route exists", function() {
        return Route::has('travel-requests.index');
    });
    
    // Test 10: Profile route exists
    runTest("Profile route exists", function() {
        return Route::has('profile.show');
    });
    
    echo "\n4. 🔒 SECURITY TESTING\n";
    echo "=====================\n";
    
    // Test 11: App key is set
    runTest("App key is set", function() {
        $key = config('app.key');
        return !empty($key) && $key !== 'base64:';
    });
    
    // Test 12: Debug mode is off
    runTest("Debug mode is off", function() {
        return !config('app.debug');
    });
    
    // Test 13: Session lifetime is reasonable
    runTest("Session lifetime is reasonable", function() {
        $lifetime = config('session.lifetime');
        return $lifetime > 0 && $lifetime <= 120;
    });
    
    echo "\n5. 📁 FILE SYSTEM TESTING\n";
    echo "========================\n";
    
    // Test 14: Storage directory exists
    runTest("Storage directory exists", function() {
        return is_dir(storage_path());
    });
    
    // Test 15: Bootstrap cache directory exists
    runTest("Bootstrap cache directory exists", function() {
        return is_dir(base_path('bootstrap/cache'));
    });
    
    // Test 16: Views directory exists
    runTest("Views directory exists", function() {
        return is_dir(resource_path('views'));
    });
    
    echo "\n6. 🎨 ASSET TESTING\n";
    echo "==================\n";
    
    // Test 17: Vite manifest exists
    runTest("Vite manifest exists", function() {
        return file_exists(public_path('build/manifest.json'));
    });
    
    // Test 18: CSS assets exist
    runTest("CSS assets exist", function() {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        return !empty($manifest) && isset($manifest['resources/css/app.css']);
    });
    
    echo "\n7. 🔧 CONFIGURATION TESTING\n";
    echo "==========================\n";
    
    // Test 19: Database configuration
    runTest("Database configuration", function() {
        return !empty(config('database.default'));
    });
    
    // Test 20: Cache configuration
    runTest("Cache configuration", function() {
        return !empty(config('cache.default'));
    });
    
    // Test 21: Session configuration
    runTest("Session configuration", function() {
        return !empty(config('session.driver'));
    });
    
    echo "\n8. 📊 DATA INTEGRITY TESTING\n";
    echo "===========================\n";
    
    // Test 22: User table structure
    runTest("User table has required columns", function() {
        $user = new User();
        $fillable = $user->getFillable();
        $required = ['name', 'email', 'password', 'role'];
        return count(array_intersect($required, $fillable)) === count($required);
    });
    
    // Test 23: TravelRequest table structure
    runTest("TravelRequest table exists", function() {
        try {
            TravelRequest::count();
            return true;
        } catch (Exception $e) {
            return false;
        }
    });
    
    // Test 24: Approval table structure
    runTest("Approval table exists", function() {
        try {
            Approval::count();
            return true;
        } catch (Exception $e) {
            return false;
        }
    });
    
    // Test 25: Document table structure
    runTest("Document table exists", function() {
        try {
            Document::count();
            return true;
        } catch (Exception $e) {
            return false;
        }
    });
    
    echo "\n9. 🔄 MIDDLEWARE TESTING\n";
    echo "======================\n";
    
    // Test 26: Auth middleware registered
    runTest("Auth middleware registered", function() {
        $middleware = config('app.middleware');
        return isset($middleware['auth']);
    });
    
    // Test 27: Admin middleware registered
    runTest("Admin middleware registered", function() {
        $middleware = config('app.middleware');
        return isset($middleware['admin']);
    });
    
    echo "\n10. 🌐 APPLICATION TESTING\n";
    echo "=========================\n";
    
    // Test 28: Laravel version
    runTest("Laravel version is current", function() {
        $version = app()->version();
        return version_compare($version, '10.0.0', '>=');
    });
    
    // Test 29: Application name
    runTest("Application name is set", function() {
        return !empty(config('app.name'));
    });
    
    // Test 30: Timezone is set
    runTest("Timezone is set", function() {
        return !empty(config('app.timezone'));
    });
    
    echo "\n📊 TEST RESULTS SUMMARY\n";
    echo "======================\n";
    echo "Total Tests: " . count($tests) . "\n";
    echo "Passed: {$passed}\n";
    echo "Failed: {$failed}\n";
    echo "Success Rate: " . round(($passed / count($tests)) * 100, 1) . "%\n\n";
    
    if ($failed === 0) {
        echo "🎉 ALL TESTS PASSED! SYSTEM IS READY FOR PRODUCTION!\n";
        echo "==================================================\n";
    } else {
        echo "⚠️ SOME TESTS FAILED. PLEASE CHECK THE ISSUES ABOVE.\n";
        echo "==================================================\n";
        
        echo "\nFailed Tests:\n";
        foreach ($tests as $test) {
            if ($test['status'] === 'FAILED') {
                echo "- {$test['name']}";
                if (isset($test['error'])) {
                    echo " (Error: {$test['error']})";
                }
                echo "\n";
            }
        }
    }
    
    echo "\n🚀 NEXT STEPS:\n";
    echo "=============\n";
    if ($failed === 0) {
        echo "1. ✅ System is ready for production\n";
        echo "2. 🌐 Deploy to production server\n";
        echo "3. 📧 Configure email settings\n";
        echo "4. 🔒 Set up SSL certificate\n";
        echo "5. 📚 Create user documentation\n";
    } else {
        echo "1. 🔧 Fix failed tests\n";
        echo "2. 🔄 Re-run tests\n";
        echo "3. ✅ Ensure all tests pass\n";
        echo "4. 🌐 Then deploy to production\n";
    }
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Please check your system configuration.\n";
}
?> 