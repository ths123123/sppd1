// Dashboard specific JavaScript

import { dashboardAPI } from '../services/api.js';
import { formatCurrency } from '../utils/helpers.js';

export class DashboardManager {
    constructor() {
        this.chartInstances = {};
        this.autoRefreshInterval = null;
        this.init();
    }

    init() {
        this.loadDashboardData();
        this.initializeCharts();
        this.loadRecentActivities();
        this.setupAutoRefresh();
        this.bindEvents();
    }

    async loadDashboardData() {
        try {
            const stats = await dashboardAPI.getStats();
            this.updateStatistics(stats);
        } catch (error) {
            console.error('Failed to load dashboard stats:', error);
            this.showNotification('Gagal memuat data statistik', 'error');

            // Use fallback data
            this.updateStatistics({
                total_sppd: 0,
                pending_approval: 0,
                approved_sppd: 0,
                total_budget: 0
            });
        }
    }

    updateStatistics(stats) {
        // Update statistic cards
        const statElements = {
            'total-sppd': stats.total_sppd || 0,
            'pending-approval': stats.pending_approval || 0,
            'approved-sppd': stats.approved_sppd || 0,
            'total-budget': stats.total_budget || 0
        };

        Object.entries(statElements).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                if (id === 'total-budget') {
                    element.textContent = formatCurrency(value);
                } else {
                    element.textContent = value.toLocaleString('id-ID');
                }

                // Add animation
                element.classList.add('fade-in');
            }
        });
    }

    async initializeCharts() {
        await this.loadMonthlyChart();
        await this.loadStatusChart();
        await this.loadBudgetChart();
    }

    async loadMonthlyChart() {
        try {
            const data = await dashboardAPI.getChartData('monthly');
            this.renderMonthlyChart(data);
        } catch (error) {
            console.error('Failed to load monthly chart:', error);
            // Use fallback data
            this.renderMonthlyChart({
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                values: [0, 0, 0, 0, 0, 0]
            });
        }
    }

    async loadStatusChart() {
        try {
            const data = await dashboardAPI.getChartData('status');
            this.renderStatusChart(data);
        } catch (error) {
            console.error('Failed to load status chart:', error);
            // Use fallback data
            this.renderStatusChart({
                labels: ['Approved', 'Pending', 'Rejected'],
                values: [0, 0, 0]
            });
        }
    }

    async loadBudgetChart() {
        try {
            const data = await dashboardAPI.getChartData('budget');
            this.renderBudgetChart(data);
        } catch (error) {
            console.error('Failed to load budget chart:', error);
            // Use fallback data
            this.renderBudgetChart({
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                values: [0, 0, 0, 0, 0, 0]
            });
        }
    }

    renderMonthlyChart(data) {
        const ctx = document.getElementById('monthlyChart');
        if (!ctx) {
            console.warn('Monthly chart canvas not found');
            return;
        }

        try {
            // Destroy existing chart if exists
            if (this.chartInstances.monthly) {
                this.chartInstances.monthly.destroy();
            }

            this.chartInstances.monthly = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'SPPD per Bulan',
                        data: data.values || [],
                        borderColor: '#4F46E5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#4F46E5',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverRadius: 8
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error rendering monthly chart:', error);
        }
    }

    renderStatusChart(data) {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;

        // Destroy existing chart if exists
        if (this.chartInstances.status) {
            this.chartInstances.status.destroy();
        }

        this.chartInstances.status = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels || [],
                datasets: [{
                    data: data.values || [],
                    backgroundColor: [
                        '#10B981', // Approved - Green
                        '#F59E0B', // Pending - Yellow
                        '#EF4444', // Rejected - Red
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    renderBudgetChart(data) {
        const ctx = document.getElementById('budgetChart');
        if (!ctx) return;

        // Destroy existing chart if exists
        if (this.chartInstances.budget) {
            this.chartInstances.budget.destroy();
        }

        this.chartInstances.budget = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels || [],
                datasets: [{
                    label: 'Budget (Rp)',
                    data: data.values || [],
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderColor: '#4F46E5',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    setupAutoRefresh() {
        // Refresh dashboard every 30 seconds
        this.autoRefreshInterval = setInterval(() => {
            this.loadDashboardData();
            this.loadRecentActivities();
        }, 30000);
    }

    async loadRecentActivities() {
        try {
            const activities = await dashboardAPI.getRecentActivities();
            if (activities && activities.length > 0) {
                // Import function from charts.js
                if (typeof updateRecentActivities === 'function') {
                    updateRecentActivities(activities);
                }
            }
        } catch (error) {
            console.error('Failed to load recent activities:', error);
        }
    }

    bindEvents() {
        // Refresh button
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshDashboard();
            });
        }

        // Chart period selector
        const periodSelector = document.getElementById('chartPeriod');
        if (periodSelector) {
            periodSelector.addEventListener('change', (e) => {
                this.updateChartPeriod(e.target.value);
            });
        }
    }

    async refreshDashboard() {
        const refreshBtn = document.getElementById('refreshDashboard');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
        }

        try {
            await this.loadDashboardData();
            await this.initializeCharts();
            await this.loadRecentActivities();

            // Show success notification
            this.showNotification('Dashboard berhasil diperbarui', 'success');
        } catch (error) {
            this.showNotification('Gagal memperbarui dashboard', 'error');
        } finally {
            if (refreshBtn) {
                refreshBtn.disabled = false;
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
            }
        }
    }

    async updateChartPeriod(period) {
        try {
            const data = await dashboardAPI.getChartData('monthly', { period });
            this.renderMonthlyChart(data);
        } catch (error) {
            console.error('Failed to update chart period:', error);
        }
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} fade-in`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 1000;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        `;
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    destroy() {
        // Clear auto refresh
        if (this.autoRefreshInterval) {
            clearInterval(this.autoRefreshInterval);
        }

        // Destroy chart instances
        Object.values(this.chartInstances).forEach(chart => {
            if (chart) chart.destroy();
        });
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    try {
        if (document.querySelector('.dashboard-grid')) {
            window.dashboardManager = new DashboardManager();
        }
    } catch (error) {
        console.error('Error initializing dashboard:', error);
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    try {
        if (window.dashboardManager) {
            window.dashboardManager.destroy();
        }
    } catch (error) {
        console.error('Error destroying dashboard:', error);
    }
});
