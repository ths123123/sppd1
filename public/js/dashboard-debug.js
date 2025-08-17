// Dashboard Debug Script
// This script helps debug dashboard issues

console.log('🔧 Dashboard Debug Script Loaded');

// Function to check dashboard status
function checkDashboardStatus() {
    console.log('🔍 Dashboard Status Check:');

    // Check required functions
    const functions = [
        'loadRecentActivities',
        'updateRecentActivities',
        'fetchRealtimeDashboard',
        'showEmptyActivityMessage'
    ];

    functions.forEach(funcName => {
        const isAvailable = typeof window[funcName] === 'function';
        console.log(`- ${funcName}: ${isAvailable ? '✅ Available' : '❌ Missing'}`);
    });

    // Check DOM elements
    const activityContainer = document.querySelector('#recent-activities-container');
    console.log('- Activity Container:', activityContainer ? '✅ Found' : '❌ Missing');

    if (activityContainer) {
        console.log('- Container HTML Length:', activityContainer.innerHTML.length);
        console.log('- Container Classes:', activityContainer.className);
    }

    // Check if charts.js is loaded
    const chartsScript = document.querySelector('script[src*="charts"]');
    console.log('- Charts Script:', chartsScript ? '✅ Loaded' : '❌ Not Found');

    return {
        functions: functions.map(f => ({ name: f, available: typeof window[f] === 'function' })),
        container: !!activityContainer,
        chartsLoaded: !!chartsScript
    };
}

// Function to manually test API
async function testRecentActivitiesAPI() {
    console.log('🧪 Testing Recent Activities API...');

    try {
        const response = await fetch('/dashboard/recent-activities');
        console.log('API Response Status:', response.status);
        console.log('API Response OK:', response.ok);

        if (response.ok) {
            const result = await response.json();
            console.log('API Response Data:', result);

            if (result.success && result.data) {
                console.log('✅ API returned activities:', result.data.length);
                return result.data;
            } else {
                console.warn('⚠️ API returned no activities or error');
                return null;
            }
        } else {
            console.error('❌ API request failed');
            return null;
        }
    } catch (error) {
        console.error('❌ API test error:', error);
        return null;
    }
}

// Function to manually update activities
function manualUpdateActivities(activities) {
    console.log('🔧 Manually updating activities...');

    if (!activities || activities.length === 0) {
        console.log('📝 No activities to display');
        showEmptyActivityMessage();
        return;
    }

        const activityContainer = document.querySelector('#recent-activities-container');
    if (!activityContainer) {
        console.error('❌ Activity container not found');
        return;
    }

    // Check if activities are already displayed
    const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
    if (existingActivities.length > 0) {
        console.log('📝 Activities already displayed, skipping manual update');
        return;
    }

    console.log(`📋 Rendering ${activities.length} activities`);

    let html = '';
    activities.forEach((activity, index) => {
        console.log(`📝 Processing activity ${index + 1}:`, activity);

        // Determine icon and color based on status
        let bgColor = 'bg-blue-100';
        let textColor = 'text-blue-600';
        let icon = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';

        if (activity.status === 'completed') {
            bgColor = 'bg-green-100';
            textColor = 'text-green-600';
            icon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
        } else if (activity.status === 'rejected') {
            bgColor = 'bg-red-100';
            textColor = 'text-red-600';
            icon = 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
        } else if (activity.status === 'in_review') {
            bgColor = 'bg-purple-100';
            textColor = 'text-purple-600';
            icon = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
        } else if (activity.status === 'revision') {
            bgColor = 'bg-yellow-100';
            textColor = 'text-yellow-600';
            icon = 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z';
        } else if (activity.status === 'submitted') {
            bgColor = 'bg-blue-100';
            textColor = 'text-blue-600';
            icon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
        }

        html += `
        <div class="px-6 py-5 flex items-start hover:bg-gray-50 transition-colors duration-150">
            <div class="flex-shrink-0">
                <span class="h-12 w-12 rounded-full ${bgColor} flex items-center justify-center shadow-md">
                    <svg class="h-7 w-7 ${textColor}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icon}" />
                    </svg>
                </span>
            </div>
            <div class="ml-5 flex-1">
                <div class="flex justify-between items-start">
                    <p class="text-sm font-medium text-gray-900">${activity.description || 'Aktivitas SPPD'}</p>
                    <span class="text-xs text-gray-500 ml-2 whitespace-nowrap font-medium">${activity.time_ago || activity.updated_at_diff || ''}</span>
                </div>
                <div class="mt-1">
                    <p class="text-xs text-gray-500">
                        <span class="font-medium">${activity.kode_sppd || 'No. SPPD belum tersedia'}</span>
                        ${activity.tujuan ? `- ${activity.tujuan}` : ''}
                    </p>
                </div>
                ${activity.approver_name ? `
                <div class="mt-1">
                    <p class="text-xs text-gray-500">
                        <span class="font-medium">Diproses oleh: ${activity.approver_name}</span>
                        ${activity.appver_role ? `(${activity.approver_role})` : ''}
                    </p>
                </div>` : ''}
            </div>
        </div>
        `;
    });

    activityContainer.innerHTML = html;
    console.log('✅ Activities updated successfully');
}

// Function to show empty message
function showEmptyActivityMessage() {
    console.log('📝 Showing empty activity message');
    const activityContainer = document.querySelector('#recent-activities-container');
    if (activityContainer) {
        // Check if activities are already displayed
        const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
        if (existingActivities.length > 0) {
            console.log('📝 Activities already displayed, skipping empty message');
            return;
        }

        activityContainer.innerHTML = `
            <div class="flex flex-col items-center justify-center py-10 px-6 text-center">
                <svg class="h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada aktivitas terbaru</h3>
                <p class="mt-1 text-sm text-gray-500">Aktivitas SPPD terbaru akan muncul di sini.</p>
            </div>
        `;
    } else {
        console.error('❌ Activity container not found');
    }
}

// Auto-run status check after page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM loaded, running dashboard status check...');

    // Wait a bit for other scripts to load
    setTimeout(() => {
        const status = checkDashboardStatus();

        // If functions are missing, try to load activities manually
        if (!status.functions.every(f => f.available)) {
            console.log('⚠️ Some functions are missing, attempting manual load...');

            // Check if activities are already displayed
            const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
            if (existingActivities.length > 0) {
                console.log('✅ Activities already displayed, skipping manual load');
                return;
            }

            testRecentActivitiesAPI().then(activities => {
                if (activities) {
                    manualUpdateActivities(activities);
                } else {
                    showEmptyActivityMessage();
                }
            });
        }
    }, 2000);
});

// Export functions to global scope for debugging
window.checkDashboardStatus = checkDashboardStatus;
window.testRecentActivitiesAPI = testRecentActivitiesAPI;
window.manualUpdateActivities = manualUpdateActivities;
window.showEmptyActivityMessage = showEmptyActivityMessage;

console.log('🔧 Dashboard Debug Script Ready - Use checkDashboardStatus() to debug');
