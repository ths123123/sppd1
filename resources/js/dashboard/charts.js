/**
 * ====================================================================
 * DASHBOARD CHARTS MODULE - SISTEM SPPD KPU KABUPATEN CIREBON
 * ====================================================================
 *
 * 📊 PROFESSIONAL CHART MANAGEMENT
 *
 * 🎯 FEATURES:
 * - Monthly trend analysis
 * - Status distribution visualization
 * - Real-time data integration
 * - Responsive chart rendering
 * - Fallback data handling
 *
 * 🔧 DEPENDENCIES:
 * - Chart.js v3+
 * - Backend data integration
 *
 * ====================================================================
 */

class DashboardCharts {
    constructor(data) {
        this.data = data;
        this.charts = {};
        this.initialize();
    }

    /**
     * Initialize all dashboard charts
     */
    initialize() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded!');
            return;
        }

        this.initializeMonthlyChart();
        this.initializeStatusChart();
        console.log('Dashboard charts initialized successfully');
    }

    /**
     * Initialize monthly trend chart
     */
    initializeMonthlyChart() {
        const ctx = document.getElementById('monthlyChart');
        if (!ctx) return;

        // Use real data or fallback
        const months = this.data.months.length > 0 ? this.data.months : this.getDefaultMonths();
        const approved = this.data.monthlyApproved.length > 0 ? this.data.monthlyApproved : this.getDefaultData();
        const inReview = this.data.monthlyInReview.length > 0 ? this.data.monthlyInReview : this.getDefaultData();
        const rejected = this.data.monthlyRejected.length > 0 ? this.data.monthlyRejected : this.getDefaultData();
        const submitted = this.data.monthlySubmitted.length > 0 ? this.data.monthlySubmitted : this.getDefaultData();

        this.charts.monthly = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'SPPD Disetujui',
                        data: approved,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    },
                    {
                        label: 'SPPD Diajukan',
                        data: inReview,
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245,158,11,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: '#F59E0B',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    },
                    {
                        label: 'SPPD Ditolak',
                        data: rejected,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239,68,68,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: '#EF4444',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    },
                    {
                        label: 'Total Diajukan',
                        data: submitted,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    /**
     * Initialize status distribution chart
     */
    initializeStatusChart() {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;

        const distribution = this.data.statusDistribution;

        this.charts.status = new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Disetujui', 'Diajukan', 'Ditinjau', 'Ditolak'],
                datasets: [{
                    data: [
                        distribution.approved || 1,
                        distribution.submitted || 2,
                        distribution.in_review || 1,
                        distribution.rejected || 1
                    ],
                    backgroundColor: [
                        '#10B981', // Hijau untuk disetujui
                        '#3B82F6', // Biru untuk diajukan
                        '#F59E0B', // Oranye untuk ditinjau
                        '#EC4899'  // Pink untuk ditolak
                    ],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    /**
     * Update charts with new data
     */
    updateCharts(newData) {
        this.data = { ...this.data, ...newData };

        if (this.charts.monthly) {
            this.charts.monthly.destroy();
        }

        if (this.charts.status) {
            this.charts.status.destroy();
        }

        this.initializeMonthlyChart();
        this.initializeStatusChart();
    }

    /**
     * Get default months for fallback
     */
    getDefaultMonths() {
        return [
            'Jul 2024', 'Agu 2024', 'Sep 2024', 'Okt 2024',
            'Nov 2024', 'Des 2024', 'Jan 2025', 'Feb 2025',
            'Mar 2025', 'Apr 2025', 'Mei 2025', 'Jun 2025'
        ];
    }

    /**
     * Get default data array for fallback
     */
    getDefaultData() {
        return [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    }

    /**
     * Destroy all charts
     */
    destroy() {
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        this.charts = {};
    }
}

/**
 * Dashboard Statistics Manager
 */
class DashboardStatistics {
    constructor() {
        this.elements = this.initializeElements();
    }

    /**
     * Initialize DOM elements
     */
    initializeElements() {
        return {
            approved: document.getElementById('approved-count'),
            pending: document.getElementById('submitted-count'),
            review: document.getElementById('review-count'),
            document: document.getElementById('document-count')
        };
    }

    /**
     * Update statistics display
     * Accepts either statusDistribution or full statistics object
     */
    updateStatistics(data) {
        // If data has statistics, use it
        if (data.statistics) data = data.statistics;
        if (this.elements.approved) {
            this.elements.approved.textContent = data.completed ?? data.approved ?? 0;
        }
        if (this.elements.pending) {
            this.elements.pending.textContent = data.submitted ?? 0;
        }
        if (this.elements.review) {
            this.elements.review.textContent = data.review ?? 0;
        }
        if (this.elements.document) {
            this.elements.document.textContent = data.documents ?? data.document ?? 0;
        }
    }
}

/**
 * Main Dashboard Manager
 */
window.DashboardManager = {
    charts: null,
    statistics: null,

    /**
     * Initialize dashboard
     */
    init(data) {
        this.charts = new DashboardCharts(data);
        this.statistics = new DashboardStatistics();

        console.log('Dashboard Manager initialized');
        console.log('Data received:', data);
    },

    /**
     * Refresh dashboard data
     */
    refresh(newData) {
        if (this.charts) {
            this.charts.updateCharts(newData);
        }
        if (this.statistics) {
            this.statistics.updateStatistics(newData.statusDistribution || {});
        }
    },

    /**
     * Cleanup dashboard
     */
    destroy() {
        if (this.charts) {
            this.charts.destroy();
        }
    }
};

// === AUTO-REFRESH DASHBOARD DATA ===

// Fungsi untuk memperbarui aktivitas terbaru
function updateRecentActivities(activities) {
    const activityContainer = document.querySelector('#recent-activities-container');
    if (!activityContainer) return;
    
    // Hapus konten sebelumnya
    activityContainer.innerHTML = '';
    
    if (activities && activities.length > 0) {
        let html = '';
        
        activities.forEach(activity => {
            // Tentukan warna ikon dan path berdasarkan status
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
    } else {
        // Tampilkan pesan jika tidak ada aktivitas
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
}

// Fungsi untuk mengambil data dashboard secara real-time
function fetchRealtimeDashboard() {
    fetch('/api/dashboard/realtime', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Cache-Control': 'no-cache'
        },
        credentials: 'same-origin'
    })
        .then(res => {
            if (res.status === 401) {
                throw new Error('Not authenticated. Please login again.');
            } else if (!res.ok) {
                throw new Error(`HTTP error! Status: ${res.status}`);
            }
            const contentType = res.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return res.json();
            } else {
                throw new Error('Invalid response format');
            }
        })
        .then(res => {
            if (res && res.success && res.data) {
                // Update charts dan statistik
                if (window.DashboardManager) {
                    window.DashboardManager.refresh({
                        months: res.data.monthly_trend.months,
                        monthlyApproved: res.data.monthly_trend.completed,
                        monthlyInReview: res.data.monthly_trend.in_review,
                        monthlyRejected: res.data.monthly_trend.rejected,
                        monthlySubmitted: res.data.monthly_trend.submitted,
                        statusDistribution: {
                            approved: res.data.statistics.completed,
                            submitted: res.data.statistics.submitted,
                            review: res.data.statistics.review,
                            rejected: res.data.statistics.rejected,
                            document: res.data.statistics.documents,
                            completed: res.data.statistics.completed // Tambahkan completed untuk kompatibilitas
                        }
                    });
                }
                
                // Update aktivitas terbaru
                updateRecentActivities(res.data.recent_activities);
                
                // Update statistik card
                document.getElementById('approved-count').textContent = res.data.statistics.completed || 0;
                document.getElementById('rejected-count').textContent = res.data.statistics.rejected || 0;
                document.getElementById('review-count').textContent = res.data.statistics.review || 0;
                document.getElementById('submitted-count').textContent = res.data.statistics.submitted || 0;
                
                // Update waktu terakhir diperbarui
                const lastUpdatedElement = document.querySelector('.text-white.text-lg.font-bold');
                if (lastUpdatedElement) {
                    lastUpdatedElement.textContent = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                }
                
                const lastUpdatedTimeElement = document.querySelector('.text-white.text-opacity-80.text-sm');
                if (lastUpdatedTimeElement) {
                    lastUpdatedTimeElement.textContent = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                }
            }
        })
        .catch(err => {
            console.error('Failed to fetch realtime dashboard data:', err);
            // Tampilkan pesan kesalahan yang lebih informatif
            if (err.message.includes('Not authenticated')) {
                // Redirect ke halaman login jika tidak terotentikasi
                window.location.href = '/login';
            }
        });
}

// Auto-refresh every 1 minute
setInterval(fetchRealtimeDashboard, 60000);
// Fetch once on page load
fetchRealtimeDashboard();
