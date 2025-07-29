// resources/js/pages/analytics.js

// Pastikan Chart.js sudah tersedia di halaman

class AnalyticsPage {
    constructor() {
        this.charts = {};
        this.periodSelector = document.querySelector('select[name="period"]');
        this.init();
    }

    init() {
        console.log('AnalyticsPage init started');
        
        // Check if required elements exist
        const requiredElements = [
            'monthlyTrendsChart',
            'total-sppd',
            'approved-sppd', 
            'rejected-sppd',
            'review-sppd',
            'total-change',
            'approved-change',
            'rejected-change',
            'review-change'
        ];
        
        const missingElements = requiredElements.filter(id => !document.getElementById(id));
        if (missingElements.length > 0) {
            console.error('Missing required elements:', missingElements);
        } else {
            console.log('All required elements found');
        }
        
        if (this.periodSelector) {
            this.periodSelector.addEventListener('change', (e) => {
                this.fetchAndRender(e.target.value);
            });
        }
        
        // Initialize chart type selector
        this.initChartTypeSelector();
        
        // Initialize timeframe selector
        this.initTimeframeSelector();
        
        // Initial load
        this.fetchAndRender(this.periodSelector ? this.periodSelector.value : '12');
        this.bindChartClicks();
        this.bindModalClose();
        
        console.log('AnalyticsPage init completed');
    }

    // Initialize chart type selector
    initChartTypeSelector() {
        const chartTypeSelector = document.getElementById('chartTypeSelector');
        if (chartTypeSelector) {
            console.log('Chart type selector found, adding event listener');
            chartTypeSelector.addEventListener('change', (e) => {
                console.log('Chart type changed to:', e.target.value);
                // Re-render chart with new type
                this.renderMonthlyTrends(this.currentData?.monthlyTrends || []);
            });
        } else {
            console.error('Chart type selector not found');
        }
    }

    // Initialize timeframe selector
    initTimeframeSelector() {
        const timeframeButtons = document.querySelectorAll('.timeframe-btn');
        timeframeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                // Remove active class from all buttons
                timeframeButtons.forEach(b => {
                    b.classList.remove('active', 'bg-indigo-600', 'text-white');
                    b.classList.add('bg-gray-100', 'text-gray-700');
                });
                
                // Add active class to clicked button
                btn.classList.add('active', 'bg-indigo-600', 'text-white');
                btn.classList.remove('bg-gray-100', 'text-gray-700');
                
                // Fetch data for selected period
                const period = btn.getAttribute('data-period');
                this.fetchAndRender(period);
            });
        });
    }

    async fetchAndRender(period) {
        try {
            const res = await fetch(`/analytics/data?period=${period}`);
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            this.currentData = data; // Store data for updateSummaryCards
            this.renderAll(data);
            // Update waktu terakhir diperbarui
            const now = new Date();
            const formatted = now.toLocaleString('id-ID', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
            const lastUpdated = document.getElementById('last-updated');
            if (lastUpdated) lastUpdated.innerText = formatted;
        } catch (err) {
            this.showError('Gagal memuat data analytics: ' + err.message);
        }
    }

    renderAll(data) {
        console.log('renderAll called with data:', data);
        
        this.renderMonthlyTrends(data.monthlyTrends);
        this.renderBudgetTrends(data.monthlyTrends);
        this.renderStatusDistribution(data.statusDistribution);
        this.renderDepartmentAnalysis(data.departmentAnalysis);
        this.renderApprovalPerformance(data.approvalPerformance);
        this.renderTopDestinations(data.trendingData.top_destinations);
        this.renderBudgetUtilization(data.overview && data.overview.budget_utilization ? data.overview.budget_utilization : null);
        this.renderInsights(data);
        
        // Ensure summary cards are updated with the latest data
        this.updateSummaryCards(data.monthlyTrends);
    }

    // Enhanced Monthly Trends Chart with CoinMarketCap-style features
    renderMonthlyTrends(monthlyTrends) {
        console.log('renderMonthlyTrends called with:', monthlyTrends);
        
        const ctx = document.getElementById('monthlyTrendsChart');
        if (!ctx) {
            console.error('monthlyTrendsChart canvas not found');
            return;
        }
        
        const labels = (monthlyTrends || []).map(x => x.period);
        const totalSppd = (monthlyTrends || []).map(x => x.sppd_count);
        const approved = (monthlyTrends || []).map(x => x.approved_count || 0);
        const rejected = (monthlyTrends || []).map(x => x.rejected_count || 0);
        const inReview = (monthlyTrends || []).map(x => x.in_review_count || 0);
        const revision = (monthlyTrends || []).map(x => x.revision_count || 0);
        const budget = (monthlyTrends || []).map(x => x.total_budget || 0);
        const approvalRate = (monthlyTrends || []).map(x => x.approval_rate || 0);
        
        console.log('Processed data:', {
                labels,
            totalSppd,
            approved,
            rejected,
            inReview,
            revision
        });
        
        // Get current chart type from selector
        const chartTypeSelector = document.getElementById('chartTypeSelector');
        const currentChartType = chartTypeSelector ? chartTypeSelector.value : 'sppd_count';
        
        console.log('Current chart type:', currentChartType);
        
        // Prepare datasets based on chart type
        let datasets = [];
        
        switch(currentChartType) {
            case 'sppd_count':
                datasets = [
                    {
                        label: 'Disetujui',
                        data: approved,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: 'rgb(34, 197, 94)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        fill: false,
                        order: 1
                    },
                    {
                        label: 'Ditolak',
                        data: rejected,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: 'rgb(239, 68, 68)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        fill: false,
                        order: 2
                    },
                    {
                        label: 'Dalam Review',
                        data: inReview,
                        borderColor: 'rgb(245, 158, 11)',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: 'rgb(245, 158, 11)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        fill: false,
                        order: 3
                    },
                    {
                        label: 'Revisi',
                        data: revision,
                        borderColor: 'rgb(168, 85, 247)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: 'rgb(168, 85, 247)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        fill: false,
                        order: 4
                    },
                    {
                        label: 'Total SPPD',
                        data: totalSppd,
                        borderColor: 'rgba(59, 130, 246, 0.1)',
                        backgroundColor: 'transparent',
                        tension: 0.4,
                        borderWidth: 1,
                        pointBackgroundColor: 'transparent',
                        pointBorderColor: 'rgba(59, 130, 246, 0.1)',
                        pointBorderWidth: 1,
                        pointRadius: 2,
                        pointHoverRadius: 3,
                        fill: false,
                        order: 5
                    }
                ];
                break;
                
            case 'budget':
                datasets = [
                    {
                        label: 'Total Anggaran (Rp)',
                        data: budget,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        fill: false
                    }
                ];
                break;
                
            case 'approval_rate':
                datasets = [
                    {
                        label: 'Tingkat Approval (%)',
                        data: approvalRate,
                        borderColor: 'rgb(236, 72, 153)',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: 'rgb(236, 72, 153)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        fill: false
                    }
                ];
                break;
        }
        
        // Destroy existing charts
        if (this.charts.monthlyTrends) {
            console.log('Destroying existing monthlyTrends chart');
            this.charts.monthlyTrends.destroy();
        }
        
        console.log('Creating new chart with datasets:', datasets);
        
        // Main Chart
        this.charts.monthlyTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: 'rgba(255, 255, 255, 0.2)',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (currentChartType === 'budget') {
                                        label += 'Rp ' + context.parsed.y.toLocaleString();
                                    } else if (currentChartType === 'approval_rate') {
                                        label += context.parsed.y.toFixed(1) + '%';
                                    } else {
                                        label += context.parsed.y + ' SPPD';
                                    }
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
                            text: 'Periode'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: currentChartType === 'budget' ? 'Anggaran (Rp)' : 
                                  currentChartType === 'approval_rate' ? 'Tingkat Approval (%)' : 'Jumlah SPPD'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        beginAtZero: true
                    }
                },
                elements: {
                    point: {
                        hoverRadius: 8,
                        radius: 6
                    },
                    line: {
                        tension: 0.4
                    }
                }
            }
        });
        
        // Update summary cards with real data
        this.updateSummaryCards(monthlyTrends);
        
        // Update insight
        this.updateMonthlyInsight(monthlyTrends);
    }

    // Enhanced monthly insight with comprehensive analysis
    updateMonthlyInsight(monthlyTrends) {
        if (!monthlyTrends || monthlyTrends.length === 0) return;
        
        const latest = monthlyTrends[monthlyTrends.length - 1];
        const previous = monthlyTrends.length > 1 ? monthlyTrends[monthlyTrends.length - 2] : null;
        
        let insight = '';
        
        // Get current chart type
        const chartTypeSelector = document.getElementById('chartTypeSelector');
        const currentChartType = chartTypeSelector ? chartTypeSelector.value : 'sppd_count';
        
        if (currentChartType === 'sppd_count') {
            insight = this.generateSppdCountInsight(latest, previous, monthlyTrends);
        } else if (currentChartType === 'budget') {
            insight = this.generateBudgetInsight(latest, previous, monthlyTrends);
        } else if (currentChartType === 'approval_rate') {
            insight = this.generateApprovalRateInsight(latest, previous, monthlyTrends);
        }
        
        const insightElement = document.getElementById('insight-monthly');
        if (insightElement) {
            insightElement.innerHTML = insight;
        }
    }

    // Generate insight for SPPD count
    generateSppdCountInsight(latest, previous, allData) {
        const totalSppd = latest.sppd_count || 0;
        const approvedCount = latest.approved_count || 0;
        const rejectedCount = latest.rejected_count || 0;
        const inReviewCount = latest.in_review_count || 0;
        const revisionCount = latest.revision_count || 0;
        
        let insight = `📊 <strong>Analisis Tren SPPD Bulanan:</strong><br>`;
        insight += `• Total SPPD periode ini: <strong>${totalSppd}</strong> SPPD<br>`;
        insight += `• Breakdown: Disetujui <strong>${approvedCount}</strong>, Ditolak <strong>${rejectedCount}</strong>, Review <strong>${inReviewCount}</strong>, Revisi <strong>${revisionCount}</strong><br>`;
        
        if (previous) {
            const totalChange = ((totalSppd - previous.sppd_count) / previous.sppd_count * 100).toFixed(1);
            const approvalRate = totalSppd > 0 ? ((approvedCount / totalSppd) * 100).toFixed(1) : 0;
            const rejectionRate = totalSppd > 0 ? ((rejectedCount / totalSppd) * 100).toFixed(1) : 0;
            
            insight += `• Perubahan dari bulan lalu: <strong>${totalChange}%</strong><br>`;
            insight += `• Tingkat approval: <strong>${approvalRate}%</strong>, Tingkat rejection: <strong>${rejectionRate}%</strong><br>`;
        }
        
        // Trend analysis
        if (allData.length >= 3) {
            const recent = allData.slice(-3);
            const avgRecent = recent.reduce((sum, item) => sum + (item.sppd_count || 0), 0) / recent.length;
            const trend = avgRecent > (totalSppd * 1.1) ? 'meningkat' : avgRecent < (totalSppd * 0.9) ? 'menurun' : 'stabil';
            insight += `• Tren 3 bulan terakhir: <strong>${trend}</strong> (rata-rata ${avgRecent.toFixed(1)} SPPD/bulan)`;
        }
        
        return insight;
    }

    // Generate insight for budget
    generateBudgetInsight(latest, previous, allData) {
        const totalBudget = latest.total_budget || 0;
        const avgBudgetPerSppd = latest.avg_budget_per_sppd || 0;
        const maxBudget = latest.max_budget || 0;
        const minBudget = latest.min_budget || 0;
        
        let insight = `💰 <strong>Analisis Tren Anggaran Bulanan:</strong><br>`;
        insight += `• Total anggaran periode ini: <strong>Rp ${totalBudget.toLocaleString()}</strong><br>`;
        insight += `• Rata-rata anggaran per SPPD: <strong>Rp ${avgBudgetPerSppd.toLocaleString()}</strong><br>`;
        insight += `• Range anggaran: Rp ${minBudget.toLocaleString()} - Rp ${maxBudget.toLocaleString()}<br>`;
        
        if (previous) {
            const budgetChange = ((totalBudget - previous.total_budget) / previous.total_budget * 100).toFixed(1);
            insight += `• Perubahan anggaran dari bulan lalu: <strong>${budgetChange}%</strong><br>`;
        }
        
        // Budget efficiency analysis
        const efficiency = totalBudget > 0 ? ((latest.approved_count || 0) / (latest.sppd_count || 1) * 100).toFixed(1) : 0;
        insight += `• Efisiensi anggaran (approval rate): <strong>${efficiency}%</strong>`;
        
        return insight;
    }

    // Generate insight for approval rate
    generateApprovalRateInsight(latest, previous, allData) {
        const approvalRate = latest.approval_rate || 0;
        const totalSppd = latest.sppd_count || 0;
        const approvedCount = latest.approved_count || 0;
        
        let insight = `✅ <strong>Analisis Tingkat Approval Bulanan:</strong><br>`;
        insight += `• Tingkat approval periode ini: <strong>${approvalRate}%</strong><br>`;
        insight += `• Total SPPD: <strong>${totalSppd}</strong>, Disetujui: <strong>${approvedCount}</strong><br>`;
        
        if (previous) {
            const rateChange = (approvalRate - previous.approval_rate).toFixed(1);
            const changeIndicator = rateChange >= 0 ? '📈' : '📉';
            insight += `• Perubahan tingkat approval: <strong>${changeIndicator} ${rateChange}%</strong><br>`;
        }
        
        // Performance analysis
        let performance = '';
        if (approvalRate >= 80) performance = 'Sangat Baik';
        else if (approvalRate >= 60) performance = 'Baik';
        else if (approvalRate >= 40) performance = 'Cukup';
        else performance = 'Perlu Perbaikan';
        
        insight += `• Performa approval: <strong>${performance}</strong>`;
        
        return insight;
    }

    // 2. Grafik Tren Anggaran Bulanan (Bar Chart)
    renderBudgetTrends(monthlyTrends) {
        const ctx = document.getElementById('budgetTrendsChart');
        if (!ctx) return;
        const labels = (monthlyTrends || []).map(x => x.period);
        const budget = (monthlyTrends || []).map(x => x.total_budget);
        if (this.charts.budgetTrends) this.charts.budgetTrends.destroy();
        this.charts.budgetTrends = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Anggaran (Rp)',
                        data: budget,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        if (!labels.length) this.showEmpty(ctx, 'Tidak ada data anggaran');
    }

    // 3. Grafik Distribusi Status SPPD (Doughnut Chart)
    renderStatusDistribution(statusDistribution) {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;
        const labels = Object.keys(statusDistribution || {});
        const values = Object.values(statusDistribution || {});
        if (this.charts.status) this.charts.status.destroy();
        this.charts.status = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',   // completed - green
                        'rgba(239, 68, 68, 0.8)',   // rejected - red
                        'rgba(59, 130, 246, 0.8)',  // submitted - blue
                        'rgba(245, 158, 11, 0.8)',  // in_review - yellow
                        'rgba(139, 92, 246, 0.8)'   // revision_minor - purple
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
        if (!labels.length) this.showEmpty(ctx, 'Tidak ada data status');
    }

    // 4. Grafik Analisis per Departemen (Horizontal Bar Chart)
    renderDepartmentAnalysis(departmentAnalysis) {
        const ctx = document.getElementById('departmentAnalysisChart');
        if (!ctx) return;
        const labels = (departmentAnalysis || []).map(x => x.department);
        const total = (departmentAnalysis || []).map(x => x.total_requests);
        const approved = (departmentAnalysis || []).map(x => x.approved_count);
        if (this.charts.department) this.charts.department.destroy();
        this.charts.department = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total SPPD',
                        data: total,
                        backgroundColor: 'rgba(59, 130, 246, 0.4)'
                    },
                    {
                        label: 'Disetujui',
                        data: approved,
                        backgroundColor: 'rgba(34, 197, 94, 0.7)'
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true } },
                scales: { x: { beginAtZero: true } }
            }
        });
        if (!labels.length) this.showEmpty(ctx, 'Tidak ada data departemen');
    }

    // 5. Grafik Performa Approval (Pie Chart)
    renderApprovalPerformance(approvalPerformance) {
        const ctx = document.getElementById('approvalPerformanceChart');
        if (!ctx) return;
        const labels = (approvalPerformance || []).map(x => x.approver_name);
        const approvals = (approvalPerformance || []).map(x => x.total_approvals);
        if (this.charts.approval) this.charts.approval.destroy();
        this.charts.approval = new Chart(ctx, {
            type: 'pie',
            data: {
                labels,
                datasets: [{
                    data: approvals,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(139, 92, 246, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
        if (!labels.length) this.showEmpty(ctx, 'Tidak ada data approval');
    }

    // 6. Grafik Destinasi Populer (Bar Chart)
    renderTopDestinations(destinations) {
        const ctx = document.getElementById('topDestinationsChart');
        if (!ctx) return;
        const labels = (destinations || []).map(x => x.tujuan);
        const counts = (destinations || []).map(x => x.count);
        if (this.charts.destinations) this.charts.destinations.destroy();
        this.charts.destinations = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Jumlah Kunjungan',
                    data: counts,
                    backgroundColor: 'rgba(59, 130, 246, 0.4)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
        if (!labels.length) this.showEmpty(ctx, 'Tidak ada data destinasi');
    }

    // 7. Grafik Utilisasi Anggaran (Doughnut Chart)
    renderBudgetUtilization(util) {
        const ctx = document.getElementById('budgetUtilizationChart');
        if (!ctx || !util) return;
        const used = util.used || 0;
        const remaining = util.remaining || 0;
        if (this.charts.budgetUtilization) this.charts.budgetUtilization.destroy();
        this.charts.budgetUtilization = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Terpakai', 'Sisa'],
                datasets: [{
                    data: [used, remaining],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    // Analisis/Insight Otomatis Bahasa Indonesia
    renderInsights(data) {
        // 1. Insight Tren Bulanan
        const monthly = data.monthlyTrends || [];
        let insightMonthly = 'Belum ada data SPPD.';
        if (monthly.length > 1) {
            const last = monthly[monthly.length-1];
            const prev = monthly[monthly.length-2];
            const diff = last.sppd_count - prev.sppd_count;
            if (diff > 0) {
                insightMonthly = `Jumlah SPPD bulan ${last.period} meningkat ${diff} dibanding bulan sebelumnya.`;
            } else if (diff < 0) {
                insightMonthly = `Jumlah SPPD bulan ${last.period} menurun ${Math.abs(diff)} dibanding bulan sebelumnya.`;
            } else {
                insightMonthly = `Jumlah SPPD bulan ${last.period} sama dengan bulan sebelumnya.`;
            }
        } else if (monthly.length === 1) {
            insightMonthly = `Terdapat ${monthly[0].sppd_count} SPPD pada ${monthly[0].period}.`;
        }
        document.getElementById('insight-monthly').innerText = insightMonthly;

        // Helper untuk format rupiah dengan titik dan koma
        function formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // 2. Insight Anggaran
        let insightBudget = 'Belum ada data anggaran.';
        if (monthly.length > 1) {
            const last = monthly[monthly.length-1];
            const prev = monthly[monthly.length-2];
            const diff = last.total_budget - prev.total_budget;
            if (diff > 0) {
                insightBudget = `Total anggaran bulan ${last.period} naik ${formatRupiah(diff)} dibanding bulan sebelumnya.`;
            } else if (diff < 0) {
                insightBudget = `Total anggaran bulan ${last.period} turun ${formatRupiah(Math.abs(diff))} dibanding bulan sebelumnya.`;
            } else {
                insightBudget = `Total anggaran bulan ${last.period} sama dengan bulan sebelumnya.`;
            }
        } else if (monthly.length === 1) {
            insightBudget = `Total anggaran bulan ${monthly[0].period}: ${formatRupiah(monthly[0].total_budget)}.`;
        }
        document.getElementById('insight-budget').innerText = insightBudget;

        // 3. Insight Status
        const status = data.statusDistribution || {};
        let maxStatus = null, maxVal = 0, totalStatus = 0;
        Object.entries(status).forEach(([k,v])=>{if(v>maxVal){maxVal=v;maxStatus=k;} totalStatus+=v;});
        let insightStatus = 'Belum ada data status.';
        if (maxStatus) {
            insightStatus = `Status SPPD terbanyak adalah "${maxStatus}" (${maxVal} dari ${totalStatus} SPPD).`;
        }
        document.getElementById('insight-status').innerText = insightStatus;

        // 4. Insight Departemen
        const dept = data.departmentAnalysis || [];
        let insightDept = 'Belum ada data departemen.';
        if (dept.length > 0) {
            const top = dept.reduce((a,b)=>a.total_requests>b.total_requests?a:b);
            insightDept = `Departemen dengan SPPD terbanyak: ${top.department} (${top.total_requests} SPPD).`;
        }
        document.getElementById('insight-department').innerText = insightDept;

        // 5. Insight Approval
        const approval = data.approvalPerformance || [];
        let insightApproval = 'Belum ada data approval.';
        if (approval.length > 0) {
            const top = approval.reduce((a,b)=>a.total_approvals>b.total_approvals?a:b);
            insightApproval = `Approval terbanyak oleh ${top.approver_name} (${top.total_approvals} approval).`;
        }
        document.getElementById('insight-approval').innerText = insightApproval;

        // 6. Insight Destinasi
        const dest = data.trendingData && data.trendingData.top_destinations ? data.trendingData.top_destinations : [];
        let insightDest = 'Belum ada data destinasi.';
        if (dest.length > 0) {
            const top = dest[0];
            insightDest = `Destinasi paling populer: ${top.tujuan} (${top.count} kali perjalanan).`;
        }
        document.getElementById('insight-destination').innerText = insightDest;

        // 7. Insight Utilisasi Anggaran
        const util = data.overview && data.overview.budget_utilization ? data.overview.budget_utilization : null;
        let insightUtil = 'Belum ada data utilisasi anggaran.';
        if (util) {
            insightUtil = `Anggaran terpakai: ${formatRupiah(util.used)} dari total alokasi ${formatRupiah(util.allocated)} (${util.utilization_rate}% terpakai).`;
        }
        document.getElementById('insight-utilization').innerText = insightUtil;
    }

    showEmpty(ctx, msg) {
        // Tampilkan pesan di tengah chart jika data kosong
        if (ctx && ctx.parentNode) {
            ctx.parentNode.querySelectorAll('.empty-chart-msg').forEach(e => e.remove());
            const div = document.createElement('div');
            div.className = 'empty-chart-msg';
            div.style = 'position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#888;text-align:center;z-index:10;pointer-events:none;';
            div.innerText = msg;
            ctx.parentNode.appendChild(div);
        }
    }

    showError(msg) {
        alert(msg);
    }

    bindChartClicks() {
        // Monthly Trends
        const monthly = document.getElementById('monthlyTrendsChart');
        if (monthly) {
            monthly.onclick = (evt) => {
                const points = this.charts.monthlyTrends.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const idx = points[0].index;
                    const label = this.charts.monthlyTrends.data.labels[idx];
                    this.showDetailModal('monthly', { period: label });
                }
            };
        }
        // Budget Trends
        const budget = document.getElementById('budgetTrendsChart');
        if (budget) {
            budget.onclick = (evt) => {
                const points = this.charts.budgetTrends.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const idx = points[0].index;
                    const label = this.charts.budgetTrends.data.labels[idx];
                    this.showDetailModal('budget', { period: label });
                }
            };
        }
        // Status Distribution
        const status = document.getElementById('statusChart');
        if (status) {
            status.onclick = (evt) => {
                const points = this.charts.status.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const idx = points[0].index;
                    const label = this.charts.status.data.labels[idx];
                    this.showDetailModal('status', { status: label });
                }
            };
        }
        // Department Analysis
        const dept = document.getElementById('departmentAnalysisChart');
        if (dept) {
            dept.onclick = (evt) => {
                const points = this.charts.department.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const idx = points[0].index;
                    const label = this.charts.department.data.labels[idx];
                    this.showDetailModal('department', { department: label });
                }
            };
        }
        // Approval Performance
        const approval = document.getElementById('approvalPerformanceChart');
        if (approval) {
            approval.onclick = (evt) => {
                const points = this.charts.approval.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const idx = points[0].index;
                    const label = this.charts.approval.data.labels[idx];
                    this.showDetailModal('approval', { approver: label });
                }
            };
        }
        // Top Destinations
        const dest = document.getElementById('topDestinationsChart');
        if (dest) {
            dest.onclick = (evt) => {
                const points = this.charts.destinations.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const idx = points[0].index;
                    const label = this.charts.destinations.data.labels[idx];
                    this.showDetailModal('destination', { tujuan: label });
                }
            };
        }
        // Budget Utilization
        const util = document.getElementById('budgetUtilizationChart');
        if (util) {
            util.onclick = () => {
                this.showDetailModal('utilization', {});
            };
        }
    }

    showDetailModal(type, params) {
        const modal = document.getElementById('analytics-modal');
        const content = document.getElementById('analytics-modal-content');
        modal.classList.remove('hidden');
        content.innerHTML = `<div class='text-center text-lg font-bold mb-4'>Memuat detail...</div>`;
        // Simpan type & params untuk AJAX detail berikutnya
        content.dataset.detailType = type;
        content.dataset.detailParams = JSON.stringify(params);
        // AJAX ambil data detail
        const url = new URL('/analytics/detail', window.location.origin);
        url.searchParams.set('type', type);
        Object.entries(params).forEach(([k,v])=>url.searchParams.set(k,v));
        fetch(url)
            .then(res => res.json())
            .then(data => {
                let html = `<div class='text-xl font-bold mb-2'>${data.title}</div>`;
                if (data.columns && data.columns.length) {
                    html += `<div class='overflow-x-auto'><table class='min-w-full text-sm border rounded-lg'>`;
                    html += `<thead><tr>${data.columns.map(c=>`<th class='px-3 py-2 bg-slate-100 border-b text-left'>${c}</th>`).join('')}</tr></thead>`;
                    html += `<tbody>`;
                    if (data.data && data.data.length) {
                        data.data.forEach(row => {
                            html += `<tr>${row.map(cell=>`<td class='px-3 py-2 border-b'>${cell}</td>`).join('')}</tr>`;
                        });
                    } else {
                        html += `<tr><td colspan='${data.columns.length}' class='text-center py-4 text-gray-400'>Tidak ada data.</td></tr>`;
                    }
                    html += `</tbody></table></div>`;
                } else {
                    html += `<div class='text-gray-400 text-center py-8'>Tidak ada data detail.</div>`;
                }
                content.innerHTML = html;
            })
            .catch(() => {
                content.innerHTML = `<div class='text-red-500 text-center py-8'>Gagal memuat detail data.</div>`;
            });
    }

    bindModalClose() {
        const modal = document.getElementById('analytics-modal');
        const closeBtn = document.getElementById('close-analytics-modal');
        if (closeBtn) {
            closeBtn.onclick = () => {
                modal.classList.add('hidden');
            };
        }
        // Tutup modal jika klik di luar konten
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });
    }

    // Update summary cards with change indicators
    updateSummaryCards(monthlyTrends) {
        console.log('updateSummaryCards called with:', monthlyTrends);
        
        if (!monthlyTrends || monthlyTrends.length === 0) {
            console.log('No monthly trends data available');
            return;
        }
        
        const latest = monthlyTrends[monthlyTrends.length - 1];
        console.log('Latest data:', latest);
        
        // If we have at least 2 data points, calculate changes
        if (monthlyTrends.length >= 2) {
            const previous = monthlyTrends[monthlyTrends.length - 2];
            console.log('Previous data:', previous);
            
            // Calculate changes
            const totalChange = this.calculateChange(latest.sppd_count, previous.sppd_count);
            const approvedChange = this.calculateChange(latest.approved_count, previous.approved_count);
            const rejectedChange = this.calculateChange(latest.rejected_count, previous.rejected_count);
            const reviewChange = this.calculateChange(latest.in_review_count, previous.in_review_count);
            
            console.log('Changes calculated:', { totalChange, approvedChange, rejectedChange, reviewChange });
            
            // Update cards with values and changes
            this.updateCard('total-sppd', latest.sppd_count, totalChange, 'total-change');
            this.updateCard('approved-sppd', latest.approved_count, approvedChange, 'approved-change');
            this.updateCard('rejected-sppd', latest.rejected_count, rejectedChange, 'rejected-change');
            this.updateCard('review-sppd', latest.in_review_count, reviewChange, 'review-change');
        } else {
            console.log('Only one data point available, showing current values with 0% change');
            // If only one data point, just show the values without changes
            this.updateCard('total-sppd', latest.sppd_count, '0%', 'total-change');
            this.updateCard('approved-sppd', latest.approved_count, '0%', 'approved-change');
            this.updateCard('rejected-sppd', latest.rejected_count, '0%', 'rejected-change');
            this.updateCard('review-sppd', latest.in_review_count, '0%', 'review-change');
        }
    }

    // Calculate percentage change
    calculateChange(current, previous) {
        if (previous === 0) return current > 0 ? '+100%' : '0%';
        const change = ((current - previous) / previous) * 100;
        return change >= 0 ? `+${change.toFixed(1)}%` : `${change.toFixed(1)}%`;
    }

    // Update individual card
    updateCard(cardId, value, change, changeId) {
        console.log(`updateCard called: ${cardId} = ${value}, ${changeId} = ${change}`);
        
        const cardElement = document.getElementById(cardId);
        const changeElement = document.getElementById(changeId);
        
        console.log(`Card element found:`, !!cardElement);
        console.log(`Change element found:`, !!changeElement);
        
        if (cardElement) {
            cardElement.textContent = value || 0;
            console.log(`Updated ${cardId} to:`, value || 0);
        } else {
            console.error(`Card element not found: ${cardId}`);
        }
        
        if (changeElement) {
            changeElement.textContent = change;
            // Color code the change
            if (change.startsWith('+')) {
                changeElement.className = 'text-xs text-green-600';
            } else if (change.startsWith('-')) {
                changeElement.className = 'text-xs text-red-600';
            } else {
                changeElement.className = 'text-xs text-gray-500';
            }
            console.log(`Updated ${changeId} to:`, change);
        } else {
            console.error(`Change element not found: ${changeId}`);
        }
    }
}

// Inisialisasi jika ada elemen analytics
if (document.getElementById('monthlyTrendsChart')) {
    window.analyticsPage = new AnalyticsPage();
} 