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
                labels: ['Disetujui', 'Ditinjau', 'Ditolak', 'Selesai'],
                datasets: [{
                    data: [
                        distribution.approved || 1,
                        distribution.in_review || 1,
                        distribution.rejected || 1,
                        distribution.completed || 3
                    ],
                    backgroundColor: [
                        '#3B82F6',
                        '#F59E0B',
                        '#EC4899',
                        '#10B981'
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
            pending: document.getElementById('pending-count'),
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
            this.elements.pending.textContent = data.pending ?? 0;
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
function fetchRealtimeDashboard() {
    fetch('/api/dashboard/realtime')
        .then(res => {
            const contentType = res.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return res.json();
            } else {
                throw new Error('Not authenticated or invalid response');
            }
        })
        .then(res => {
            if (res && res.success && res.data) {
                if (window.DashboardManager) {
                    window.DashboardManager.refresh({
                        months: res.data.monthly_trend.months,
                        monthlyApproved: res.data.monthly_trend.completed,
                        monthlySubmitted: res.data.monthly_trend.in_review,
                        statusDistribution: {
                            approved: res.data.statistics.completed,
                            pending: res.data.statistics.pending,
                            review: res.data.statistics.review,
                            document: res.data.statistics.documents
                        }
                    });
                }
            }
        })
        .catch(err => {
            console.error('Failed to fetch realtime dashboard data:', err);
        });
}

// Auto-refresh every 1 minute
setInterval(fetchRealtimeDashboard, 60000);
// Optionally, fetch once on page load
fetchRealtimeDashboard();
