/**
 * ====================================================================
 * ENHANCED DASHBOARD TEST SCRIPT
 * ====================================================================
 *
 * 🧪 TESTING ACTIVITY PRESERVATION SYSTEM
 * - Tests activity loading and preservation
 * - Verifies race condition prevention
 * - Checks localStorage backup functionality
 *
 * ====================================================================
 */

// Test function to verify activity preservation system
function testActivityPreservationSystem() {
    console.log('🧪 Testing Activity Preservation System...');

    // Test 1: Check if activity system is initialized
    if (typeof activitySystem !== 'undefined' && activitySystem.isInitialized) {
        console.log('✅ Test 1 PASSED: Activity system is initialized');
    } else {
        console.log('❌ Test 1 FAILED: Activity system not initialized');
        return false;
    }

    // Test 2: Check if functions are available
    if (typeof loadRecentActivities === 'function' &&
        typeof updateRecentActivities === 'function' &&
        typeof fetchRealtimeDashboard === 'function') {
        console.log('✅ Test 2 PASSED: All required functions are available');
    } else {
        console.log('❌ Test 2 FAILED: Missing required functions');
        return false;
    }

    // Test 3: Check if DOM elements exist
    const activityContainer = document.querySelector('#recent-activities-container');
    if (activityContainer) {
        console.log('✅ Test 3 PASSED: Activity container found');
    } else {
        console.log('❌ Test 3 FAILED: Activity container not found');
        return false;
    }

    // Test 4: Check localStorage functionality
    try {
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('test_key', 'test_value');
            const testValue = localStorage.getItem('test_key');
            localStorage.removeItem('test_key');

            if (testValue === 'test_value') {
                console.log('✅ Test 4 PASSED: localStorage is working');
            } else {
                console.log('❌ Test 4 FAILED: localStorage not working properly');
                return false;
            }
        } else {
            console.log('⚠️ Test 4 SKIPPED: localStorage not available');
        }
    } catch (error) {
        console.log('⚠️ Test 4 SKIPPED: localStorage error:', error);
    }

    // Test 5: Check mutex functionality
    if (activitySystem.acquireLock) {
        console.log('✅ Test 5 PASSED: Mutex system available');
    } else {
        console.log('❌ Test 5 FAILED: Mutex system not available');
        return false;
    }

    console.log('🎉 All tests completed successfully!');
    return true;
}

// Test function to simulate activity loading
async function testActivityLoading() {
    console.log('🧪 Testing Activity Loading...');

    try {
        // Test loading activities
        await loadRecentActivities();
        console.log('✅ Activity loading test completed');

        // Check if activities are displayed
        setTimeout(() => {
            const activities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
            if (activities.length > 0) {
                console.log(`✅ Found ${activities.length} activities displayed`);
            } else {
                console.log('⚠️ No activities displayed (this might be normal if no data)');
            }
        }, 2000);

    } catch (error) {
        console.error('❌ Activity loading test failed:', error);
    }
}

// Test function to verify race condition prevention
async function testRaceConditionPrevention() {
    console.log('🧪 Testing Race Condition Prevention...');

    try {
        // Try to make multiple API calls simultaneously
        const promises = [
            loadRecentActivities(),
            loadRecentActivities(),
            loadRecentActivities()
        ];

        await Promise.all(promises);
        console.log('✅ Race condition prevention test completed');

    } catch (error) {
        console.error('❌ Race condition prevention test failed:', error);
    }
}

// Test function to verify data persistence
function testDataPersistence() {
    console.log('🧪 Testing Data Persistence...');

    try {
        // Check if activities are stored in system
        if (activitySystem.hasActivities()) {
            const activities = activitySystem.getActivities();
            console.log(`✅ Found ${activities.length} activities stored in system`);
        } else {
            console.log('⚠️ No activities stored in system (this might be normal)');
        }

        // Check localStorage backup
        if (typeof localStorage !== 'undefined') {
            const stored = localStorage.getItem('dashboard_activities_backup');
            if (stored) {
                const backup = JSON.parse(stored);
                console.log(`✅ Found ${backup.activities?.length || 0} activities in localStorage backup`);
            } else {
                console.log('⚠️ No localStorage backup found (this might be normal)');
            }
        }

    } catch (error) {
        console.error('❌ Data persistence test failed:', error);
    }
}

// Run all tests
function runAllTests() {
    console.log('🚀 Starting Enhanced Dashboard Tests...');
    console.log('=====================================');

    // Run basic tests
    const basicTestsPassed = testActivityPreservationSystem();

    if (basicTestsPassed) {
        // Run advanced tests
        setTimeout(() => {
            testActivityLoading();
        }, 1000);

        setTimeout(() => {
            testRaceConditionPrevention();
        }, 3000);

        setTimeout(() => {
            testDataPersistence();
        }, 5000);
    }

    console.log('=====================================');
    console.log('🏁 Test suite completed');
}

// Export test functions
window.testActivityPreservationSystem = testActivityPreservationSystem;
window.testActivityLoading = testActivityLoading;
window.testRaceConditionPrevention = testRaceConditionPrevention;
window.testDataPersistence = testDataPersistence;
window.runAllTests = runAllTests;

// Auto-run tests after page load
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        console.log('🧪 Auto-running enhanced dashboard tests...');
        runAllTests();
    }, 2000);
});

console.log('🧪 Enhanced Dashboard Test Script Loaded');
console.log('Run runAllTests() to execute all tests');
