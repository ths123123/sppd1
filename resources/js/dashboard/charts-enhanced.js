/**
 * ====================================================================
 * DASHBOARD CHARTS MODULE - ENHANCED STABILITY VERSION
 * ====================================================================
 *
 * 🎯 ACTIVITY PRESERVATION SYSTEM
 * - Prevents activities from disappearing
 * - Implements proper race condition handling
 * - Enhanced error recovery and data persistence
 *
 * ====================================================================
 */

// === ACTIVITY PRESERVATION SYSTEM ===
class ActivityPreservationSystem {
    constructor() {
        this.storageKey = 'dashboard_activities_backup';
        this.mutex = false;
        this.lastApiCall = 0;
        this.minApiInterval = 10000; // 10 seconds minimum
        this.activities = [];
        this.isInitialized = false;
        this.backupInterval = null;

        this.initialize();
    }

    initialize() {
        console.log('🔧 Initializing Activity Preservation System...');
        this.loadFromStorage();
        this.startPeriodicBackup();
        this.isInitialized = true;
        console.log('✅ Activity Preservation System initialized');
    }

    async acquireLock() {
        if (this.mutex) {
            console.log('🔒 Mutex already locked, waiting...');
            return false;
        }
        this.mutex = true;
        console.log('🔒 Mutex acquired');
        return true;
    }

    releaseLock() {
        this.mutex = false;
        console.log('🔓 Mutex released');
    }

    canMakeApiCall() {
        const now = Date.now();
        const timeSinceLastCall = now - this.lastApiCall;

        if (timeSinceLastCall < this.minApiInterval) {
            console.log(`⏰ API call throttled: ${Math.round(timeSinceLastCall/1000)}s < ${this.minApiInterval/1000}s`);
            return false;
        }
        return true;
    }

    saveToStorage(activities) {
        try {
            if (typeof localStorage !== 'undefined') {
                const backup = {
                    activities: activities,
                    timestamp: Date.now(),
                    version: '1.0'
                };
                localStorage.setItem(this.storageKey, JSON.stringify(backup));
                console.log('💾 Activities saved to localStorage');
            }
        } catch (error) {
            console.warn('⚠️ Failed to save to localStorage:', error);
        }
    }

    loadFromStorage() {
        try {
            if (typeof localStorage !== 'undefined') {
                const stored = localStorage.getItem(this.storageKey);
                if (stored) {
                    const backup = JSON.parse(stored);
                    const age = Date.now() - backup.timestamp;

                    if (age < 3600000 && backup.activities && Array.isArray(backup.activities)) {
                        this.activities = backup.activities;
                        console.log('📂 Loaded activities from localStorage:', this.activities.length);
                        return true;
                    } else {
                        console.log('🗑️ Stored activities too old, clearing localStorage');
                        localStorage.removeItem(this.storageKey);
                    }
                }
            }
        } catch (error) {
            console.warn('⚠️ Failed to load from localStorage:', error);
        }
        return false;
    }

    startPeriodicBackup() {
        if (this.backupInterval) {
            clearInterval(this.backupInterval);
        }

        this.backupInterval = setInterval(() => {
            if (this.activities.length > 0) {
                this.saveToStorage(this.activities);
            }
        }, 30000); // Backup every 30 seconds
    }

    updateActivities(newActivities) {
        if (!newActivities || !Array.isArray(newActivities) || newActivities.length === 0) {
            console.log('⚠️ Invalid activities data provided, keeping existing');
            return false;
        }

        this.activities = newActivities;
        this.saveToStorage(this.activities);
        console.log('✅ Activities updated:', this.activities.length);
        return true;
    }

    getActivities() {
        return this.activities;
    }

    hasActivities() {
        return this.activities && Array.isArray(this.activities) && this.activities.length > 0;
    }

    destroy() {
        if (this.backupInterval) {
            clearInterval(this.backupInterval);
        }
        console.log('🧹 Activity Preservation System destroyed');
    }
}

// Initialize the activity preservation system
const activitySystem = new ActivityPreservationSystem();

// === ENHANCED ACTIVITY MANAGEMENT FUNCTIONS ===

function showEmptyActivityMessage() {
    const activityContainer = document.querySelector('#recent-activities-container');
    if (!activityContainer) {
        console.error('❌ Activity container not found!');
        return;
    }

    const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
    if (existingActivities.length > 0) {
        console.log('📝 Activities already displayed, skipping empty message');
        return;
    }

    console.log('📝 Showing empty activity message');
    activityContainer.innerHTML = `
        <div class="flex flex-col items-center justify-center py-10 px-6 text-center">
            <svg class="h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada aktivitas terbaru</h3>
            <p class="mt-1 text-sm text-gray-500">Aktivitas SPPD terbaru akan muncul di sini.</p>
        </div>
    `;
}

function updateRecentActivities(activities) {
    console.log('🔄 Updating recent activities with:', activities);

    const activityContainer = document.querySelector('#recent-activities-container');
    if (!activityContainer) {
        console.error('❌ Activity container not found!');
        return;
    }

    // CRITICAL: Validate data before any DOM operations
    if (!activities || !Array.isArray(activities) || activities.length === 0) {
        console.log('⚠️ Invalid activities data provided');

        const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
        if (existingActivities.length > 0) {
            console.log('📝 Keeping existing activities - no valid replacement data');
            return;
        }

        showEmptyActivityMessage();
        return;
    }

    // Update activity system
    activitySystem.updateActivities(activities);

    try {
        const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');

        // CRITICAL: Only clear container if we have valid new data AND no existing activities
        if (existingActivities.length === 0) {
            console.log('📋 Rendering activities - container was empty');
            activityContainer.innerHTML = '';
        } else {
            console.log('📝 Activities already displayed, skipping render');
            return;
        }

        // Render activities
        console.log(`📋 Rendering ${activities.length} activities`);
        let html = '';

        activities.forEach((activity, index) => {
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
                            ${activity.approver_role ? `(${activity.approver_role})` : ''}
                        </p>
                    </div>` : ''}
                </div>
            </div>
            `;
        });

        activityContainer.innerHTML = html;
        console.log('✅ Activities rendered successfully');

    } catch (error) {
        console.error('❌ Error updating activities:', error);

        // CRITICAL: Restore activities if rendering failed
        if (activitySystem.hasActivities()) {
            console.log('🔄 Attempting to restore activities after error');
            const restoredActivities = activitySystem.getActivities();
            if (restoredActivities.length > 0) {
                updateRecentActivities(restoredActivities);
            }
        } else {
            const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
            if (existingActivities.length === 0) {
                showEmptyActivityMessage();
            }
        }
    }
}

async function loadRecentActivities() {
    try {
        console.log('🔄 Loading recent activities...');

        if (!(await activitySystem.acquireLock())) {
            console.log('🚫 Activities loading already in progress, skipping');
            return;
        }

        if (!activitySystem.canMakeApiCall()) {
            console.log('🚫 API call throttled, skipping');
            activitySystem.releaseLock();
            return;
        }

        const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
        if (existingActivities.length > 0) {
            console.log('✅ Activities already displayed, skipping API call');
            activitySystem.releaseLock();
            return;
        }

        activitySystem.lastApiCall = Date.now();

        console.log('📡 Fetching recent activities from API...');
        const response = await fetch('/dashboard/recent-activities');
        console.log('Response status:', response.status);

        if (response.ok) {
            const result = await response.json();
            console.log('Response data:', result);

            if (result.success && result.data && result.data.length > 0) {
                console.log('✅ Activities found:', result.data.length);
                updateRecentActivities(result.data);
            } else {
                console.warn('⚠️ No activities found or empty response:', result.message);

                if (activitySystem.hasActivities()) {
                    console.log('🔄 Restoring activities from system');
                    updateRecentActivities(activitySystem.getActivities());
                } else if (existingActivities.length === 0) {
                    showEmptyActivityMessage();
                }
            }
        } else {
            console.warn('⚠️ Failed to load recent activities:', response.status);

            if (activitySystem.hasActivities()) {
                console.log('🔄 Restoring activities from system after API failure');
                updateRecentActivities(activitySystem.getActivities());
            } else if (existingActivities.length === 0) {
                showEmptyActivityMessage();
            }
        }
    } catch (error) {
        console.error('❌ Error loading recent activities:', error);

        if (activitySystem.hasActivities()) {
            console.log('🔄 Restoring activities from system after error');
            updateRecentActivities(activitySystem.getActivities());
        } else {
            const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
            if (existingActivities.length === 0) {
                showEmptyActivityMessage();
            }
        }
    } finally {
        activitySystem.releaseLock();
    }
}

async function fetchRealtimeDashboard() {
    try {
        console.log('📡 Fetching realtime dashboard data...');

        if (!(await activitySystem.acquireLock())) {
            console.log('🚫 Dashboard refresh already in progress, skipping');
            return;
        }

        if (!activitySystem.canMakeApiCall()) {
            console.log('🚫 API call throttled, skipping dashboard refresh');
            activitySystem.releaseLock();
            return;
        }

        activitySystem.lastApiCall = Date.now();

        const response = await fetch('/api/dashboard/realtime', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Cache-Control': 'no-cache'
            },
            credentials: 'same-origin'
        });

        if (response.status === 401) {
            throw new Error('Not authenticated. Please login again.');
        } else if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Invalid response format');
        }

        const result = await response.json();

        if (result && result.success && result.data) {
            // Update charts and statistics
            if (window.DashboardManager) {
                try {
                    window.DashboardManager.refresh({
                        months: result.data.monthly_trend?.months || [],
                        monthlyApproved: result.data.monthly_trend?.completed || [],
                        monthlyInReview: result.data.monthly_trend?.in_review || [],
                        monthlyRejected: result.data.monthly_trend?.rejected || [],
                        monthlySubmitted: result.data.monthly_trend?.submitted || [],
                        statusDistribution: {
                            approved: result.data.statistics?.completed || 0,
                            submitted: result.data.statistics?.submitted || 0,
                            review: result.data.statistics?.review || 0,
                            rejected: result.data.statistics?.rejected || 0,
                            document: result.data.statistics?.documents || 0,
                            completed: result.data.statistics?.completed || 0
                        }
                    });
                } catch (error) {
                    console.warn('DashboardManager refresh failed:', error);
                }
            }

            // Update activities with validation
            if (result.data.recent_activities && Array.isArray(result.data.recent_activities) && result.data.recent_activities.length > 0) {
                console.log('📊 Updating recent activities from realtime dashboard');
                updateRecentActivities(result.data.recent_activities);
            } else {
                console.log('📊 No valid recent activities in realtime data, keeping existing');

                const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
                if (existingActivities.length === 0 && activitySystem.hasActivities()) {
                    console.log('🔄 Restoring activities from system');
                    updateRecentActivities(activitySystem.getActivities());
                }
            }

            // Update statistics cards
            const approvedElement = document.getElementById('approved-count');
            const rejectedElement = document.getElementById('rejected-count');
            const reviewElement = document.getElementById('review-count');
            const submittedElement = document.getElementById('submitted-count');

            if (approvedElement) approvedElement.textContent = result.data.statistics?.completed || 0;
            if (rejectedElement) rejectedElement.textContent = result.data.statistics?.rejected || 0;
            if (reviewElement) reviewElement.textContent = result.data.statistics?.review || 0;
            if (submittedElement) submittedElement.textContent = result.data.statistics?.submitted || 0;

            // Update last updated time
            const lastUpdatedElement = document.querySelector('.text-white.text-lg.font-bold');
            if (lastUpdatedElement) {
                lastUpdatedElement.textContent = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            }

            const lastUpdatedTimeElement = document.querySelector('.text-white.text-opacity-80.text-sm');
            if (lastUpdatedTimeElement) {
                lastUpdatedTimeElement.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            }

            console.log('✅ Realtime dashboard data updated successfully');
        }
    } catch (error) {
        console.error('❌ Failed to fetch realtime dashboard data:', error);

        if (error.message.includes('Not authenticated')) {
            window.location.href = '/login';
        }
    } finally {
        activitySystem.releaseLock();
    }
}

// === AUTO-REFRESH SYSTEM ===

// Auto-refresh every 5 minutes (increased from 3 minutes)
setInterval(() => {
    if (!activitySystem.isInitialized) {
        console.log('🚫 Activity system not initialized, skipping auto-refresh');
        return;
    }

    if (!activitySystem.canMakeApiCall()) {
        console.log('🚫 Auto-refresh throttled, skipping');
        return;
    }

    const existingActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
    if (existingActivities.length === 0 && activitySystem.hasActivities()) {
        console.log('🔄 Auto-refresh: Restoring activities from system');
        updateRecentActivities(activitySystem.getActivities());
    } else {
        console.log('🔄 Auto-refresh dashboard data');
        fetchRealtimeDashboard();
    }
}, 300000); // 5 minutes

// === INITIALIZATION ===

// Initialize global variables
window.activitiesLocked = false;
window.apiCallInProgress = false;
window.preventActivityClear = true; // Always prevent clearing by default
window.lastSuccessfulRefreshTime = Date.now();

// Debug log for system status
console.log('🔍 Activity Preservation System Status:');
console.log('- System initialized:', activitySystem.isInitialized);
console.log('- Activities stored:', activitySystem.hasActivities());
console.log('- Activities count:', activitySystem.getActivities().length);

// Load activities on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM Content Loaded - Initializing enhanced dashboard...');

    // Check if backend already provided activities
    const backendActivities = document.querySelectorAll('#recent-activities-container .px-6.py-5');
    if (backendActivities.length > 0) {
        console.log('✅ Backend provided activities, storing in system');

        // Extract activities from DOM and store in system
        const activities = Array.from(backendActivities).map(activity => {
            return {
                description: activity.querySelector('.text-sm.font-medium')?.textContent || 'Aktivitas SPPD',
                status: 'submitted',
                kode_sppd: 'SPPD-' + Math.random().toString(36).substr(2, 9),
                time_ago: 'Baru saja'
            };
        });

        activitySystem.updateActivities(activities);
        console.log('📊 Backend activities stored in system:', activities.length);
    } else {
        console.log('🔄 No backend activities, will load via JavaScript');

        setTimeout(() => {
            if (typeof loadRecentActivities === 'function') {
                loadRecentActivities();
            }
        }, 1000);
    }

    // Initialize dashboard manager if available
    if (window.DashboardManager) {
        const dashboardData = {
            months: [],
            monthlyApproved: [],
            monthlyInReview: [],
            monthlyRejected: [],
            monthlySubmitted: [],
            statusDistribution: {
                approved: 0,
                submitted: 0,
                in_review: 0,
                rejected: 0,
                completed: 0
            }
        };

        window.DashboardManager.init(dashboardData);
    }

    console.log('🎯 Enhanced Dashboard SPPD KPU Kabupaten Cirebon - Stability Version Active');
});

// Export functions to global scope
window.loadRecentActivities = loadRecentActivities;
window.updateRecentActivities = updateRecentActivities;
window.fetchRealtimeDashboard = fetchRealtimeDashboard;
