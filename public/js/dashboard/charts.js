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
        const submitted = this.data.monthlySubmitted.length > 0 ? this.data.monthlySubmitted : this.getDefaultData();

        // Set device pixel ratio untuk high DPI displays
        const dpr = window.devicePixelRatio || 1;
        const rect = ctx.getBoundingClientRect();

        // Set canvas size dengan device pixel ratio
        ctx.width = rect.width * dpr;
        ctx.height = rect.height * dpr;
        ctx.style.width = rect.width + 'px';
        ctx.style.height = rect.height + 'px';

        // Scale context untuk high DPI
        const context = ctx.getContext('2d');
        context.scale(dpr, dpr);

        this.charts.monthly = new Chart(context, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'SPPD Disetujui',
                        data: approved,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHitRadius: 8
                    },
                    {
                        label: 'SPPD Diajukan',
                        data: submitted,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHitRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: dpr,
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 13,
                                weight: '600',
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'
                            },
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 13,
                            weight: '500'
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + ' SPPD';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Bulan',
                            font: {
                                size: 14,
                                weight: '700',
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'
                            },
                            color: '#374151',
                            padding: 10
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.08)',
                            lineWidth: 1,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500',
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'
                            },
                            color: '#6B7280',
                            maxRotation: 45,
                            minRotation: 0,
                            padding: 8
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Jumlah SPPD',
                            font: {
                                size: 14,
                                weight: '700',
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'
                            },
                            color: '#374151',
                            padding: 10
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.08)',
                            lineWidth: 1,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12,
                                weight: '500',
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'
                            },
                            color: '#6B7280',
                            beginAtZero: true,
                            stepSize: 1,
                            padding: 8
                        },
                        border: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8,
                        hitRadius: 10,
                        borderWidth: 2
                    },
                    line: {
                        borderWidth: 2.5
                    }
                },
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                },
                layout: {
                    padding: {
                        top: 20,
                        right: 20,
                        bottom: 20,
                        left: 20
                    }
                }
            }
        });

        // Add resize observer untuk high-quality rendering
        this.setupChartResizeObserver(ctx, dpr);
    }

    /**
     * Setup resize observer untuk chart quality
     */
    setupChartResizeObserver(canvas, dpr) {
        if (window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(entries => {
                for (let entry of entries) {
                    const rect = entry.contentRect;

                    // Update canvas size dengan device pixel ratio
                    canvas.width = rect.width * dpr;
                    canvas.height = rect.height * dpr;
                    canvas.style.width = rect.width + 'px';
                    canvas.style.height = rect.height + 'px';

                    // Update chart
                    if (this.charts.monthly) {
                        this.charts.monthly.resize();
                    }
                }
            });

            resizeObserver.observe(canvas.parentElement);
        }
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
                labels: ['Disetujui', 'Menunggu', 'Ditinjau', 'Ditolak', 'Draft'],
                datasets: [{
                    data: [
                        distribution.approved || 1,
                        distribution.submitted || 2,
                        distribution.in_review || 1,
                        distribution.rejected || 1,
                        distribution.draft || 3
                    ],
                    backgroundColor: [
                        '#3B82F6',
                        '#10B981',
                        '#F59E0B',
                        '#EC4899',
                        '#6B7280'
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
     */
    updateStatistics(data) {
        if (this.elements.approved) {
            this.elements.approved.textContent = data.approved || 0;
        }
        if (this.elements.pending) {
            this.elements.pending.textContent = data.pending || 0;
        }
        if (this.elements.review) {
            this.elements.review.textContent = data.review || 0;
        }
        if (this.elements.document) {
            this.elements.document.textContent = data.document || 0;
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

// Helper function untuk update dashboard statistics dengan null check
function updateDashboardStatistics(statistics) {
    if (!statistics) return;
    
    // Update statistik card dengan null check yang lebih robust
    const elements = {
        'approved-count': statistics.completed || 0,
        'rejected-count': statistics.rejected || 0,
        'review-count': statistics.review || 0,
        'submitted-count': statistics.submitted || 0,
        'document-count': statistics.documents || 0
    };
    
    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element && element.textContent !== undefined) {
            try {
                element.textContent = value;
            } catch (error) {
                console.warn(`Failed to update ${id}:`, error);
            }
        }
    });
}

// Helper function untuk update waktu terakhir diperbarui
function updateLastUpdatedTime() {
    try {
        // Update tanggal
        const lastUpdatedElement = document.querySelector('.text-white.text-lg.font-bold');
        if (lastUpdatedElement && lastUpdatedElement.textContent !== undefined) {
            lastUpdatedElement.textContent = new Date().toLocaleDateString('id-ID', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric' 
            });
        }
        
        // Update waktu
        const lastUpdatedTimeElement = document.querySelector('.text-white.text-opacity-80.text-sm');
        if (lastUpdatedTimeElement && lastUpdatedTimeElement.textContent !== undefined) {
            lastUpdatedTimeElement.textContent = new Date().toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            }) + ' WIB';
        }
    } catch (error) {
        console.warn('Failed to update last updated time:', error);
    }
}

// Fungsi untuk memperbarui aktivitas terbaru
function updateRecentActivities(activities) {
    const activityContainer = document.querySelector('#recent-activities-container');
    if (!activityContainer) {
        console.warn('Recent activities container not found');
        return;
    }
    
    try {
        // Hapus konten sebelumnya
        activityContainer.innerHTML = '';
        
        if (activities && activities.length > 0) {
            let html = '';
            
            activities.forEach(activity => {
                // Tentukan warna ikon dan path berdasarkan status
                let bgColor = 'bg-blue-100';
                let textColor = 'text-blue-600';
                let icon = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                
                // Tentukan warna berdasarkan status aktivitas
                if (activity.status === 'completed' || activity.status === 'approved') {
                    bgColor = 'bg-green-100';
                    textColor = 'text-green-600';
                    icon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
                } else if (activity.status === 'rejected') {
                    bgColor = 'bg-red-100';
                    textColor = 'text-red-600';
                    icon = 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z';
                } else if (activity.status === 'revision') {
                    bgColor = 'bg-yellow-100';
                    textColor = 'text-yellow-600';
                    icon = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z';
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
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="mt-2 text-sm font-medium text-gray-900">Belum ada aktivitas</p>
                    <p class="mt-1 text-sm text-gray-500">Aktivitas akan muncul di sini setelah ada perubahan.</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error updating recent activities:', error);
        // Fallback content jika terjadi error
        activityContainer.innerHTML = `
            <div class="px-6 py-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <p class="mt-2 text-sm font-medium text-gray-900">Error memuat aktivitas</p>
                <p class="mt-1 text-sm text-gray-500">Terjadi kesalahan saat memuat aktivitas terbaru.</p>
            </div>
        `;
    }
}

// Fungsi untuk mengambil data dashboard secara real-time
function fetchRealtimeDashboard() {
    try {
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
                
                // Update statistik card dengan null check yang lebih robust
                updateDashboardStatistics(res.data.statistics);
                
                // Update waktu terakhir diperbarui dengan null check
                updateLastUpdatedTime();
            } else {
                console.warn('Invalid response format from dashboard API');
            }
        })
        .catch(err => {
            console.error('Failed to fetch realtime dashboard data:', err);
            // Tampilkan pesan kesalahan yang lebih informatif
            if (err.message.includes('Not authenticated')) {
                // Redirect ke halaman login jika tidak terotentikasi
                window.location.href = '/login';
            } else {
                // Log error untuk debugging
                console.error('Dashboard API Error Details:', {
                    message: err.message,
                    stack: err.stack,
                    timestamp: new Date().toISOString()
                });
            }
        });
    } catch (error) {
        console.error('Error in fetchRealtimeDashboard:', error);
    }
}

// Auto-refresh every 1 minute
setInterval(() => {
    try {
        fetchRealtimeDashboard();
    } catch (error) {
        console.error('Error in dashboard auto-refresh:', error);
    }
}, 60000);

// Fungsi untuk memuat aktivitas terbaru secara terpisah
async function loadRecentActivities() {
    try {
        const response = await fetch('/dashboard/recent-activities');
        if (response.ok) {
            const activities = await response.json();
            updateRecentActivities(activities);
        } else {
            console.warn('Failed to load recent activities:', response.status);
            // Tampilkan pesan error yang user-friendly
            const activityContainer = document.querySelector('#recent-activities-container');
            if (activityContainer) {
                activityContainer.innerHTML = `
                    <div class="px-6 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <p class="mt-2 text-sm font-medium text-gray-900">Gagal memuat aktivitas</p>
                        <p class="mt-1 text-sm text-gray-500">Silakan refresh halaman atau coba lagi nanti.</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading recent activities:', error);
        // Tampilkan pesan error yang user-friendly
        const activityContainer = document.querySelector('#recent-activities-container');
        if (activityContainer) {
            activityContainer.innerHTML = `
                <div class="px-6 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <p class="mt-2 text-sm font-medium text-gray-900">Terjadi kesalahan</p>
                    <p class="mt-1 text-sm text-gray-500">Gagal memuat aktivitas terbaru. Silakan coba lagi.</p>
                </div>
            `;
        }
    }
}

// Fetch once on page load
document.addEventListener('DOMContentLoaded', () => {
    try {
        // Load recent activities immediately
        loadRecentActivities();
        
        // Delay sedikit untuk memastikan DOM sudah siap
        setTimeout(() => {
            if (typeof fetchRealtimeDashboard === 'function') {
                fetchRealtimeDashboard();
            } else {
                console.warn('fetchRealtimeDashboard function not available');
            }
        }, 1000);
    } catch (error) {
        console.error('Error initializing dashboard:', error);
    }
});

// Add global error handler untuk dashboard
window.addEventListener('error', (event) => {
    if (event.filename && event.filename.includes('charts.js')) {
        console.error('Dashboard Charts Error:', {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            error: event.error
        });
    }
});

// Add unhandled rejection handler
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason && event.reason.message && event.reason.message.includes('dashboard')) {
        console.error('Dashboard Unhandled Promise Rejection:', event.reason);
        event.preventDefault(); // Prevent default browser error handling
    }
});
