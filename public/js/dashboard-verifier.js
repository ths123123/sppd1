/**
 * Dashboard Verifier - SISTEM SPPD KPU KABUPATEN CIREBON
 * =====================================================
 *
 * 🎯 PURPOSE: Verify dashboard functionality without affecting frontend
 * ✅ FEATURES: Console-only logging, no visual interference
 *
 * =====================================================
 */

class DashboardVerifier {
    constructor() {
        this.testResults = {
            functions: false,
            api: false,
            display: false
        };
    }

    // Verify all dashboard functions
    async verifyDashboard() {
        console.log('🔍 Dashboard Verifier Started');
        console.log('=====================================');

        // Test 1: Function availability
        this.verifyFunctions();

        // Test 2: API connectivity
        await this.verifyAPI();

        // Test 3: Display functionality
        this.verifyDisplay();

        // Show results
        this.showResults();
    }

    // Verify JavaScript functions
    verifyFunctions() {
        console.log('🧪 Testing Function Availability...');

        const functions = [
            'loadRecentActivities',
            'updateRecentActivities',
            'fetchRealtimeDashboard'
        ];

        let allAvailable = true;

        functions.forEach(funcName => {
            if (typeof window[funcName] === 'function') {
                console.log(`✅ ${funcName}: Available`);
            } else {
                console.log(`❌ ${funcName}: Missing`);
                allAvailable = false;
            }
        });

        this.testResults.functions = allAvailable;
        console.log(`📊 Functions: ${allAvailable ? 'PASS' : 'FAIL'}`);
    }

    // Verify API connectivity
    async verifyAPI() {
        console.log('🌐 Testing API Connectivity...');

        try {
            const response = await fetch('/dashboard/recent-activities');
            if (response.ok) {
                const result = await response.json();
                console.log('✅ /dashboard/recent-activities: Working');
                console.log(`📊 Response: ${result.success ? 'Success' : 'Failed'}`);
                this.testResults.api = result.success;
            } else {
                console.log(`❌ /dashboard/recent-activities: HTTP ${response.status}`);
                this.testResults.api = false;
            }
        } catch (error) {
            console.log(`❌ /dashboard/recent-activities: Error - ${error.message}`);
            this.testResults.api = false;
        }
    }

    // Verify display functionality
    verifyDisplay() {
        console.log('🎨 Testing Display Functionality...');

        const container = document.getElementById('recent-activities-container');
        if (container) {
            console.log('✅ Activity container: Found');
            console.log(`📊 Container content length: ${container.innerHTML.length}`);

            // Check if container has meaningful content
            const hasContent = container.innerHTML.length > 100;
            const hasEmptyMessage = container.innerHTML.includes('Belum ada aktivitas');

            if (hasContent && !hasEmptyMessage) {
                console.log('✅ Container: Has meaningful content');
                this.testResults.display = true;
            } else if (hasEmptyMessage) {
                console.log('⚠️ Container: Shows empty message (normal if no activities)');
                this.testResults.display = true; // This is actually correct behavior
            } else {
                console.log('❌ Container: No meaningful content');
                this.testResults.display = false;
            }
        } else {
            console.log('❌ Activity container: Not found');
            this.testResults.display = false;
        }
    }

    // Show verification results
    showResults() {
        console.log('=====================================');
        console.log('📊 DASHBOARD VERIFICATION RESULTS');
        console.log('=====================================');
        console.log(`✅ Functions: ${this.testResults.functions ? 'PASS' : 'FAIL'}`);
        console.log(`✅ API: ${this.testResults.api ? 'PASS' : 'FAIL'}`);
        console.log(`✅ Display: ${this.testResults.display ? 'PASS' : 'FAIL'}`);

        const passCount = Object.values(this.testResults).filter(Boolean).length;
        const totalTests = Object.keys(this.testResults).length;

        console.log(`🎯 Overall: ${passCount}/${totalTests} tests passed`);

        if (passCount === totalTests) {
            console.log('🎉 SUCCESS: Dashboard is working correctly!');
        } else {
            console.log('⚠️ WARNING: Some dashboard features may not be working properly');
        }

        console.log('=====================================');
    }
}

// Initialize verifier when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Wait a bit for dashboard to initialize
    setTimeout(() => {
        const verifier = new DashboardVerifier();
        verifier.verifyDashboard();
    }, 2000);
});

console.log('📦 Dashboard Verifier loaded');
