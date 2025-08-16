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
        
        // Initial load - menggunakan 'all' untuk menampilkan semua data
        this.fetchAndRender('all');
        this.bindChartClicks();
        this.bindModalClose();
        
        console.log('AnalyticsPage init completed');
    }

    // Menghapus initChartTypeSelector dan initTimeframeSelector karena tidak diperlukan lagi

    async fetchAndRender(period = 'all') {
        try {
            // Selalu menggunakan 'all' untuk menampilkan semua data
            const res = await fetch(`/analytics/data?period=all`);
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
        
        // Update semua chart dan insight dengan data terbaru
        this.updateData(data);
        
        // Ensure summary cards are updated with the latest data
        this.updateSummaryCards(data.monthlyTrends);
    }

    // Enhanced Monthly Trends Chart - Selalu menampilkan data SPPD tanpa memperhatikan filter
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
        
        console.log('Processed data:', {
            labels,
            totalSppd,
            approved,
            rejected,
            inReview,
            revision
        });
        
        // Selalu menggunakan tipe chart SPPD count tanpa memperhatikan selector
        const currentChartType = 'sppd_count';
        
        console.log('Using fixed chart type:', currentChartType);
        
        // Prepare datasets - selalu menggunakan dataset SPPD count
        const datasets = [
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
                            text: 'Jumlah SPPD'
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
        
        // Update insight dengan informasi yang lebih detail dan komprehensif
        this.updateMonthlyInsight(monthlyTrends);
    }

    // Enhanced monthly insight with comprehensive analysis dan penjelasan yang lebih detail
    updateMonthlyInsight(monthlyTrends) {
        if (!monthlyTrends || monthlyTrends.length === 0) return;
        
        const latest = monthlyTrends[monthlyTrends.length - 1];
        const previous = monthlyTrends.length > 1 ? monthlyTrends[monthlyTrends.length - 2] : null;
        
        // Selalu menggunakan insight untuk SPPD count karena chart type selector sudah dihapus
        const insight = this.generateSppdCountInsight(latest, previous, monthlyTrends);
        
        const insightElement = document.getElementById('insight-monthly');
        if (insightElement) {
            insightElement.innerHTML = insight;
        }
    }

    // Generate insight for SPPD count dengan penjelasan yang lebih detail
    generateSppdCountInsight(latest, previous, allData) {
        const totalSppd = latest.total_count || 0;
        const approvedCount = latest.approved_count || 0;
        const rejectedCount = latest.rejected_count || 0;
        const inReviewCount = latest.in_review_count || 0;
        const revisionCount = latest.revision_count || 0;
        
        let insight = `<div class="space-y-3">`;
        insight += `<p class="text-gray-800 font-medium text-base">📊 <strong>Analisis Tren SPPD Bulanan:</strong></p>`;
        
        // Informasi dasar dengan format yang lebih baik
        insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200">`;
        insight += `<p class="text-gray-700 mb-2">Total SPPD periode ini: <span class="font-medium text-indigo-600">${totalSppd}</span> SPPD</p>`;
        
        // Breakdown dengan visual yang lebih baik
        insight += `<div class="grid grid-cols-2 gap-2 text-sm">`;
        insight += `<div class="bg-green-50 p-2 rounded border border-green-200">`;
        insight += `<span class="text-green-700">Disetujui:</span> <span class="font-medium text-green-700">${approvedCount}</span>`;
        insight += `</div>`;
        
        insight += `<div class="bg-red-50 p-2 rounded border border-red-200">`;
        insight += `<span class="text-red-700">Ditolak:</span> <span class="font-medium text-red-700">${rejectedCount}</span>`;
        insight += `</div>`;
        
        insight += `<div class="bg-amber-50 p-2 rounded border border-amber-200">`;
        insight += `<span class="text-amber-700">Review:</span> <span class="font-medium text-amber-700">${inReviewCount}</span>`;
        insight += `</div>`;
        
        insight += `<div class="bg-purple-50 p-2 rounded border border-purple-200">`;
        insight += `<span class="text-purple-700">Revisi:</span> <span class="font-medium text-purple-700">${revisionCount}</span>`;
        insight += `</div>`;
        insight += `</div>`; // End grid
        insight += `</div>`; // End info box
        
        if (previous) {
            const prevTotalSppd = previous.total_count || 0;
            const prevApprovedCount = previous.approved_count || 0;
            const prevRejectedCount = previous.rejected_count || 0;
            
            // Hitung perubahan
            const totalChange = prevTotalSppd > 0 ? ((totalSppd - prevTotalSppd) / prevTotalSppd * 100).toFixed(1) : 0;
            const approvalRate = totalSppd > 0 ? ((approvedCount / totalSppd) * 100).toFixed(1) : 0;
            const rejectionRate = totalSppd > 0 ? ((rejectedCount / totalSppd) * 100).toFixed(1) : 0;
            
            // Perubahan dari bulan sebelumnya
            const isIncrease = totalSppd > prevTotalSppd;
            const changeClass = isIncrease ? 'text-green-600' : 'text-red-600';
            const changeIcon = isIncrease ? '↑' : '↓';
            
            insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mt-3">`;
            insight += `<p class="text-gray-700 mb-2">Perbandingan dengan periode sebelumnya:</p>`;
            
            insight += `<div class="space-y-2 text-sm">`;
            insight += `<p>Perubahan total SPPD: <span class="font-medium ${changeClass}">${changeIcon} ${Math.abs(totalChange)}%</span></p>`;
            insight += `<p>Tingkat persetujuan: <span class="font-medium text-green-600">${approvalRate}%</span></p>`;
            insight += `<p>Tingkat penolakan: <span class="font-medium text-red-600">${rejectionRate}%</span></p>`;
            
            // Perubahan detail
            const approvedChange = approvedCount - prevApprovedCount;
            const rejectedChange = rejectedCount - prevRejectedCount;
            
            insight += `<div class="mt-2 pt-2 border-t border-gray-200">`;
            insight += `<p class="mb-1 text-gray-600">Perubahan detail:</p>`;
            
            insight += `<p>SPPD disetujui: ${prevApprovedCount} → ${approvedCount} `;
            if (approvedChange > 0) {
                insight += `<span class="text-green-600">(+${approvedChange})</span>`;
            } else if (approvedChange < 0) {
                insight += `<span class="text-red-600">(${approvedChange})</span>`;
            } else {
                insight += `<span class="text-gray-500">(tetap)</span>`;
            }
            insight += `</p>`;
            
            insight += `<p>SPPD ditolak: ${prevRejectedCount} → ${rejectedCount} `;
            if (rejectedChange > 0) {
                insight += `<span class="text-red-600">(+${rejectedChange})</span>`;
            } else if (rejectedChange < 0) {
                insight += `<span class="text-green-600">(${rejectedChange})</span>`;
            } else {
                insight += `<span class="text-gray-500">(tetap)</span>`;
            }
            insight += `</p>`;
            
            insight += `</div>`; // End perubahan detail
            insight += `</div>`; // End space-y-2
            insight += `</div>`; // End comparison box
        }
        
        // Trend analysis dengan penjelasan yang lebih detail
        if (allData.length >= 3) {
            const recent = allData.slice(-3);
            const avgRecent = recent.reduce((sum, item) => sum + (item.total_count || 0), 0) / recent.length;
            
            let trendText = '';
            let trendClass = '';
            let trendAnalysis = '';
            
            if (avgRecent > (totalSppd * 1.1)) {
                trendText = 'menurun';
                trendClass = 'text-red-600';
                trendAnalysis = 'Terjadi penurunan jumlah SPPD dibandingkan rata-rata 3 bulan terakhir. Hal ini mungkin menunjukkan pengurangan aktivitas perjalanan dinas atau perubahan kebijakan.';
            } else if (avgRecent < (totalSppd * 0.9)) {
                trendText = 'meningkat';
                trendClass = 'text-green-600';
                trendAnalysis = 'Terjadi peningkatan jumlah SPPD dibandingkan rata-rata 3 bulan terakhir. Hal ini menunjukkan adanya peningkatan aktivitas perjalanan dinas.';
            } else {
                trendText = 'stabil';
                trendClass = 'text-blue-600';
                trendAnalysis = 'Jumlah SPPD relatif stabil dibandingkan rata-rata 3 bulan terakhir. Hal ini menunjukkan konsistensi dalam aktivitas perjalanan dinas.';
            }
            
            insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mt-3">`;
            insight += `<p class="text-gray-700 mb-2">Analisis tren 3 bulan terakhir:</p>`;
            insight += `<p>Tren: <span class="font-medium ${trendClass}">${trendText}</span> (rata-rata ${avgRecent.toFixed(1)} SPPD/bulan)</p>`;
            insight += `<p class="text-sm text-gray-600 mt-1">${trendAnalysis}</p>`;
            insight += `</div>`;
        }
        
        insight += `</div>`; // End space-y-3
        
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

    // Menghapus fungsi renderBudgetTrends

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
        if (this.charts.budgetUtilization) this.charts.budgetUtilization.destroy();
        this.charts.budgetUtilization = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Total Anggaran'],
                datasets: [{
                    data: [used],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)'
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

    // Update data dan insight dengan informasi yang lebih detail dan komprehensif
    updateData(data) {
        // Update semua insight dan chart berdasarkan data terbaru
        this.renderMonthlyTrends(data.monthlyTrends);
        this.renderStatusDistribution(data.statusDistribution);
        this.renderDepartmentAnalysis(data.departmentAnalysis);
        this.renderApprovalPerformance(data.approvalPerformance);
        this.renderTopDestinations(data.trendingData?.top_destinations);
        this.renderBudgetUtilization(data.overview?.budget_utilization);
        
        // Update insight dengan informasi yang lebih detail dan komprehensif
        this.updateMonthlyInsight(data.monthlyTrends);
        this.updateStatusInsight(data.statusDistribution);
        this.updateDepartmentInsight(data.departmentAnalysis);
        this.updateApprovalInsight(data.approvalPerformance);
        this.updateDestinationInsight(data.trendingData?.top_destinations);
        this.updateBudgetUtilizationInsight(data.overview?.budget_utilization);
    }
    
    // Update budget utilization insight dengan penjelasan yang lebih detail dan komprehensif
    updateBudgetUtilizationInsight(budgetData) {
        if (!budgetData) {
            document.getElementById('insight-utilization').innerHTML = 'Belum ada data anggaran.';
            return;
        }
        
        const totalBudget = budgetData.used || 0;
        const formattedTotal = this.formatCurrency(totalBudget);
        
        let insight = `<div class="space-y-3">`;
        insight += `<p class="text-gray-800 font-medium text-base">💰 <strong>Analisis Anggaran SPPD:</strong></p>`;
        
        // Informasi dasar dengan format yang lebih baik
        insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200">`;
        insight += `<p class="text-gray-700 mb-2">Total anggaran SPPD: <span class="font-medium text-indigo-600">${formattedTotal}</span></p>`;
        
        // Tambahkan informasi tentang jumlah SPPD dan rata-rata per SPPD jika tersedia
        if (budgetData.total_sppd && budgetData.avg_per_sppd) {
            insight += `<div class="grid grid-cols-2 gap-2 text-sm mt-2">`;
            insight += `<div class="bg-blue-50 p-2 rounded border border-blue-200">`;
            insight += `<span class="text-blue-700">Jumlah SPPD:</span> <span class="font-medium text-blue-700">${budgetData.total_sppd}</span>`;
            insight += `</div>`;
            
            insight += `<div class="bg-purple-50 p-2 rounded border border-purple-200">`;
            insight += `<span class="text-purple-700">Rata-rata per SPPD:</span> <span class="font-medium text-purple-700">${this.formatCurrency(budgetData.avg_per_sppd)}</span>`;
            insight += `</div>`;
            insight += `</div>`; // End grid
        }
        
        // Tambahkan informasi tentang distribusi anggaran berdasarkan kategori jika tersedia
        if (budgetData.categories && budgetData.categories.length > 0) {
            insight += `<div class="mt-3 pt-2 border-t border-gray-200">`;
            insight += `<p class="text-gray-700 mb-2">Distribusi anggaran:</p>`;
            insight += `<div class="overflow-x-auto">`;
            insight += `<table class="min-w-full text-sm">`;
            insight += `<thead><tr class="bg-gray-100">`;
            insight += `<th class="px-2 py-1 text-left">Kategori</th>`;
            insight += `<th class="px-2 py-1 text-right">Jumlah</th>`;
            insight += `<th class="px-2 py-1 text-right">Persentase</th>`;
            insight += `</tr></thead><tbody>`;
            
            budgetData.categories.forEach(cat => {
                const percentage = ((cat.amount / totalBudget) * 100).toFixed(1);
                insight += `<tr class="border-t border-gray-200">`;
                insight += `<td class="px-2 py-1">${cat.name}</td>`;
                insight += `<td class="px-2 py-1 text-right">${this.formatCurrency(cat.amount)}</td>`;
                insight += `<td class="px-2 py-1 text-right">${percentage}%</td>`;
                insight += `</tr>`;
            });
            
            insight += `</tbody></table>`;
            insight += `</div>`; // End overflow-x-auto
            insight += `</div>`; // End distribusi anggaran
        }
        
        // Perbandingan dengan periode sebelumnya jika tersedia
        if (budgetData.previous_total) {
            const prevTotal = budgetData.previous_total || 0;
            const change = totalBudget - prevTotal;
            const changePercent = prevTotal > 0 ? ((change / prevTotal) * 100).toFixed(1) : 0;
            const isIncrease = change >= 0;
            const changeClass = isIncrease ? 'text-green-600' : 'text-red-600';
            const changeIcon = isIncrease ? '↑' : '↓';
            const changeDirection = isIncrease ? 'meningkat' : 'menurun';
            
            insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mt-3">`;
            insight += `<p class="text-gray-700 mb-2">Perbandingan dengan periode sebelumnya:</p>`;
            
            insight += `<div class="space-y-2 text-sm">`;
            insight += `<p>Perubahan total anggaran: <span class="font-medium ${changeClass}">${changeIcon} ${Math.abs(changePercent)}%</span></p>`;
            insight += `<p>Perubahan nominal: <span class="font-medium ${changeClass}">${this.formatCurrency(Math.abs(change))}</span></p>`;
            
            // Tambahkan analisis perubahan
            insight += `<div class="mt-2 pt-2 border-t border-gray-200">`;
            insight += `<p class="text-sm text-gray-600">Anggaran SPPD ${changeDirection} sebesar ${this.formatCurrency(Math.abs(change))} (${Math.abs(changePercent)}%) dibandingkan periode sebelumnya. `;
            
            if (isIncrease) {
                insight += `Peningkatan ini menunjukkan adanya kenaikan aktivitas perjalanan dinas atau kenaikan biaya perjalanan rata-rata.</p>`;
            } else {
                insight += `Penurunan ini menunjukkan adanya pengurangan aktivitas perjalanan dinas atau efisiensi dalam biaya perjalanan.</p>`;
            }
            insight += `</div>`; // End analisis perubahan
            
            insight += `</div>`; // End space-y-2
            insight += `</div>`; // End comparison box
        }
        
        insight += `</div>`; // End info box
        insight += `</div>`; // End space-y-3
        
        const insightElement = document.getElementById('insight-utilization');
        if (insightElement) {
            insightElement.innerHTML = insight;
        }
    }
    
    // Update status insight dengan penjelasan yang lebih detail dan komprehensif
    updateStatusInsight(statusData) {
        if (!statusData || Object.keys(statusData).length === 0) {
            document.getElementById('insight-status').innerHTML = 'Belum ada data status.';
            return;
        }
        
        // Konversi data status ke format array untuk memudahkan pengolahan
        const statusArray = Object.entries(statusData).map(([status, count]) => ({ status, count }));
        // Sort by count descending
        const sortedData = [...statusArray].sort((a, b) => b.count - a.count);
        const total = sortedData.reduce((sum, item) => sum + item.count, 0);
        
        let insight = `<div class="space-y-3">`;
        insight += `<p class="text-gray-800 font-medium text-base">📋 <strong>Analisis Status SPPD:</strong></p>`;
        
        // Informasi dasar dengan format yang lebih baik
        insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200">`;
        insight += `<p class="text-gray-700 mb-2">Total SPPD: <span class="font-medium text-indigo-600">${total}</span></p>`;
        
        // Tabel distribusi status
        insight += `<div class="overflow-x-auto mt-2">`;
        insight += `<table class="min-w-full text-sm">`;
        insight += `<thead><tr class="bg-gray-100">`;
        insight += `<th class="px-2 py-1 text-left">Status</th>`;
        insight += `<th class="px-2 py-1 text-right">Jumlah</th>`;
        insight += `<th class="px-2 py-1 text-right">Persentase</th>`;
        insight += `</tr></thead><tbody>`;
        
        // Warna untuk status berbeda
        const statusColors = {
            'Disetujui': 'bg-green-50 text-green-700',
            'Ditolak': 'bg-red-50 text-red-700',
            'Dalam Review': 'bg-amber-50 text-amber-700',
            'Revisi': 'bg-purple-50 text-purple-700',
            'Draft': 'bg-gray-50 text-gray-700'
        };
        
        sortedData.forEach(status => {
            const percentage = ((status.count / total) * 100).toFixed(1);
            const colorClass = statusColors[status.status] || 'bg-gray-50 text-gray-700';
            
            insight += `<tr class="border-t border-gray-200 ${colorClass}">`;
            insight += `<td class="px-2 py-1">${status.status}</td>`;
            insight += `<td class="px-2 py-1 text-right font-medium">${status.count}</td>`;
            insight += `<td class="px-2 py-1 text-right">${percentage}%</td>`;
            insight += `</tr>`;
        });
        
        insight += `</tbody></table>`;
        insight += `</div>`; // End overflow-x-auto
        insight += `</div>`; // End info box
        
        // Analisis tingkat persetujuan dan penolakan
        const approved = sortedData.find(s => s.status === 'Disetujui');
        const rejected = sortedData.find(s => s.status === 'Ditolak');
        const inReview = sortedData.find(s => s.status === 'Dalam Review');
        
        if (approved || rejected) {
            insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mt-3">`;
            insight += `<p class="text-gray-700 mb-2">Analisis tingkat persetujuan:</p>`;
            
            const approvalRate = approved ? ((approved.count / total) * 100).toFixed(1) : 0;
            const rejectionRate = rejected ? ((rejected.count / total) * 100).toFixed(1) : 0;
            const inReviewRate = inReview ? ((inReview.count / total) * 100).toFixed(1) : 0;
            
            insight += `<div class="space-y-2 text-sm">`;
            
            if (approved) {
                insight += `<p>Tingkat persetujuan: <span class="font-medium text-green-600">${approvalRate}%</span></p>`;
            }
            
            if (rejected) {
                insight += `<p>Tingkat penolakan: <span class="font-medium text-red-600">${rejectionRate}%</span></p>`;
            }
            
            if (inReview) {
                insight += `<p>Dalam proses review: <span class="font-medium text-amber-600">${inReviewRate}%</span></p>`;
            }
            
            // Tambahkan analisis perbandingan
            insight += `<div class="mt-2 pt-2 border-t border-gray-200">`;
            insight += `<p class="text-sm text-gray-600">`;
            
            if (approved && rejected) {
                if (approved.count > rejected.count) {
                    const ratio = (approved.count / (rejected.count || 1)).toFixed(1);
                    insight += `Tingkat persetujuan ${approvalRate}% lebih tinggi dibandingkan tingkat penolakan ${rejectionRate}%. `;
                    insight += `Rasio persetujuan:penolakan adalah ${ratio}:1, menunjukkan mayoritas SPPD disetujui.`;
                } else if (rejected.count > approved.count) {
                    const ratio = (rejected.count / (approved.count || 1)).toFixed(1);
                    insight += `Tingkat penolakan ${rejectionRate}% lebih tinggi dibandingkan tingkat persetujuan ${approvalRate}%. `;
                    insight += `Rasio penolakan:persetujuan adalah ${ratio}:1, menunjukkan mayoritas SPPD ditolak.`;
                } else {
                    insight += `Tingkat persetujuan dan penolakan seimbang pada ${approvalRate}%, menunjukkan distribusi yang merata.`;
                }
            } else if (approved) {
                insight += `Tingkat persetujuan ${approvalRate}% dengan tidak ada penolakan, menunjukkan semua SPPD yang diproses telah disetujui.`;
            } else if (rejected) {
                insight += `Tingkat penolakan ${rejectionRate}% dengan tidak ada persetujuan, menunjukkan semua SPPD yang diproses telah ditolak.`;
            }
            
            insight += `</p>`;
            insight += `</div>`; // End analisis perbandingan
            
            insight += `</div>`; // End space-y-2
            insight += `</div>`; // End approval analysis box
        }
        
        insight += `</div>`; // End space-y-3
        
        const insightElement = document.getElementById('insight-status');
        if (insightElement) {
            insightElement.innerHTML = insight;
        }
    }
    
    // Update department insight dengan penjelasan yang lebih detail dan komprehensif
    updateDepartmentInsight(departmentData) {
        if (!departmentData || departmentData.length === 0) {
            document.getElementById('insight-department').innerHTML = 'Belum ada data departemen.';
            return;
        }
        
        // Sort by total requests descending
        const sortedData = [...departmentData].sort((a, b) => b.total_requests - a.total_requests);
        const topDept = sortedData[0];
        const totalSppd = sortedData.reduce((sum, dept) => sum + dept.total_requests, 0);
        
        let insight = `<div class="space-y-3">`;
        insight += `<p class="text-gray-800 font-medium text-base">🏢 <strong>Analisis Departemen:</strong></p>`;
        
        // Informasi dasar dengan format yang lebih baik
        insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200">`;
        insight += `<p class="text-gray-700 mb-2">Total SPPD dari semua departemen: <span class="font-medium text-indigo-600">${totalSppd}</span></p>`;
        
        // Informasi departemen teratas
        const topDeptApprovalRate = topDept.total_requests > 0 ? 
            ((topDept.approved_count / topDept.total_requests) * 100).toFixed(1) : 0;
        
        insight += `<div class="bg-blue-50 p-2 rounded border border-blue-200 mt-2">`;
        insight += `<p class="text-blue-700 font-medium">Departemen Teratas: ${topDept.department}</p>`;
        insight += `<div class="grid grid-cols-2 gap-2 text-sm mt-1">`;
        insight += `<div><span class="text-gray-600">Total SPPD:</span> <span class="font-medium">${topDept.total_requests}</span></div>`;
        insight += `<div><span class="text-gray-600">Disetujui:</span> <span class="font-medium text-green-600">${topDept.approved_count}</span></div>`;
        insight += `<div><span class="text-gray-600">Tingkat persetujuan:</span> <span class="font-medium">${topDeptApprovalRate}%</span></div>`;
        insight += `<div><span class="text-gray-600">Persentase dari total:</span> <span class="font-medium">${((topDept.total_requests / totalSppd) * 100).toFixed(1)}%</span></div>`;
        insight += `</div>`; // End grid
        insight += `</div>`; // End top dept box
        
        // Tabel perbandingan departemen
        insight += `<div class="overflow-x-auto mt-3">`;
        insight += `<table class="min-w-full text-sm">`;
        insight += `<thead><tr class="bg-gray-100">`;
        insight += `<th class="px-2 py-1 text-left">Departemen</th>`;
        insight += `<th class="px-2 py-1 text-right">Total</th>`;
        insight += `<th class="px-2 py-1 text-right">Disetujui</th>`;
        insight += `<th class="px-2 py-1 text-right">Tingkat</th>`;
        insight += `</tr></thead><tbody>`;
        
        sortedData.slice(0, 5).forEach(dept => {
            const approvalRate = dept.total_requests > 0 ? 
                ((dept.approved_count / dept.total_requests) * 100).toFixed(1) : 0;
            
            insight += `<tr class="border-t border-gray-200">`;
            insight += `<td class="px-2 py-1">${dept.department}</td>`;
            insight += `<td class="px-2 py-1 text-right">${dept.total_requests}</td>`;
            insight += `<td class="px-2 py-1 text-right">${dept.approved_count}</td>`;
            insight += `<td class="px-2 py-1 text-right">${approvalRate}%</td>`;
            insight += `</tr>`;
        });
        
        insight += `</tbody></table>`;
        insight += `</div>`; // End overflow-x-auto
        
        // Analisis distribusi departemen
        insight += `<div class="mt-3 pt-2 border-t border-gray-200">`;
        insight += `<p class="text-sm text-gray-600">`;
        insight += `Departemen ${topDept.department} memiliki jumlah SPPD tertinggi dengan ${topDept.total_requests} permintaan `;
        insight += `(${((topDept.total_requests / totalSppd) * 100).toFixed(1)}% dari total). `;
        
        if (sortedData.length > 1) {
            const secondDept = sortedData[1];
            const difference = topDept.total_requests - secondDept.total_requests;
            const percentDiff = ((difference / secondDept.total_requests) * 100).toFixed(1);
            
            insight += `Jumlah ini ${percentDiff}% lebih tinggi dibandingkan departemen ${secondDept.department} `;
            insight += `yang berada di posisi kedua dengan ${secondDept.total_requests} permintaan.`;
        }
        
        insight += `</p>`;
        insight += `</div>`; // End analisis distribusi
        
        insight += `</div>`; // End info box
        insight += `</div>`; // End space-y-3
        
        const insightElement = document.getElementById('insight-department');
        if (insightElement) {
            insightElement.innerHTML = insight;
        }
    }
    
    // Update approval insight dengan penjelasan yang lebih detail dan komprehensif
    updateApprovalInsight(approvalData) {
        if (!approvalData || approvalData.length === 0) {
            document.getElementById('insight-approval').innerHTML = 'Belum ada data approval.';
            return;
        }
        
        // Sort by total approvals descending
        const sortedData = [...approvalData].sort((a, b) => b.total_approvals - a.total_approvals);
        const topApprover = sortedData[0];
        const totalApprovals = sortedData.reduce((sum, approver) => sum + approver.total_approvals, 0);
        
        let insight = `<div class="space-y-3">`;
        insight += `<p class="text-gray-800 font-medium text-base">✅ <strong>Analisis Performa Approval:</strong></p>`;
        
        // Informasi dasar dengan format yang lebih baik
        insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200">`;
        insight += `<p class="text-gray-700 mb-2">Total approval: <span class="font-medium text-indigo-600">${totalApprovals}</span></p>`;
        
        // Informasi approver teratas
        insight += `<div class="bg-green-50 p-2 rounded border border-green-200 mt-2">`;
        insight += `<p class="text-green-700 font-medium">Approver Teratas: ${topApprover.approver_name}</p>`;
        insight += `<div class="grid grid-cols-2 gap-2 text-sm mt-1">`;
        insight += `<div><span class="text-gray-600">Total approval:</span> <span class="font-medium">${topApprover.total_approvals}</span></div>`;
        insight += `<div><span class="text-gray-600">Persentase dari total:</span> <span class="font-medium">${((topApprover.total_approvals / totalApprovals) * 100).toFixed(1)}%</span></div>`;
        
        if (topApprover.avg_time_to_approve) {
            insight += `<div><span class="text-gray-600">Rata-rata waktu approval:</span> <span class="font-medium">${topApprover.avg_time_to_approve}</span></div>`;
        }
        
        insight += `</div>`; // End grid
        insight += `</div>`; // End top approver box
        
        // Tabel perbandingan approver
        insight += `<div class="overflow-x-auto mt-3">`;
        insight += `<table class="min-w-full text-sm">`;
        insight += `<thead><tr class="bg-gray-100">`;
        insight += `<th class="px-2 py-1 text-left">Approver</th>`;
        insight += `<th class="px-2 py-1 text-right">Total</th>`;
        insight += `<th class="px-2 py-1 text-right">Persentase</th>`;
        insight += `</tr></thead><tbody>`;
        
        sortedData.forEach(approver => {
            const percentage = ((approver.total_approvals / totalApprovals) * 100).toFixed(1);
            
            insight += `<tr class="border-t border-gray-200">`;
            insight += `<td class="px-2 py-1">${approver.approver_name}</td>`;
            insight += `<td class="px-2 py-1 text-right">${approver.total_approvals}</td>`;
            insight += `<td class="px-2 py-1 text-right">${percentage}%</td>`;
            insight += `</tr>`;
        });
        
        insight += `</tbody></table>`;
        insight += `</div>`; // End overflow-x-auto
        
        // Analisis distribusi approval
        if (sortedData.length > 1) {
            insight += `<div class="mt-3 pt-2 border-t border-gray-200">`;
            insight += `<p class="text-sm text-gray-600">`;
            insight += `${topApprover.approver_name} telah melakukan ${topApprover.total_approvals} approval `;
            insight += `(${((topApprover.total_approvals / totalApprovals) * 100).toFixed(1)}% dari total). `;
            
            const secondApprover = sortedData[1];
            const difference = topApprover.total_approvals - secondApprover.total_approvals;
            const percentDiff = ((difference / secondApprover.total_approvals) * 100).toFixed(1);
            
            insight += `Jumlah ini ${percentDiff}% lebih tinggi dibandingkan ${secondApprover.approver_name} `;
            insight += `yang berada di posisi kedua dengan ${secondApprover.total_approvals} approval.`;
            
            insight += `</p>`;
            insight += `</div>`; // End analisis distribusi
        }
        
        insight += `</div>`; // End info box
        insight += `</div>`; // End space-y-3
        
        const insightElement = document.getElementById('insight-approval');
        if (insightElement) {
            insightElement.innerHTML = insight;
        }
    }
    
    // Update destination insight dengan penjelasan yang lebih detail dan komprehensif
    updateDestinationInsight(destinationData) {
        if (!destinationData || destinationData.length === 0) {
            document.getElementById('insight-destination').innerHTML = 'Belum ada data destinasi.';
            return;
        }
        
        const topDestination = destinationData[0];
        const totalTrips = destinationData.reduce((sum, dest) => sum + dest.count, 0);
        
        let insight = `<div class="space-y-3">`;
        insight += `<p class="text-gray-800 font-medium text-base">🌍 <strong>Analisis Destinasi SPPD:</strong></p>`;
        
        // Informasi dasar dengan format yang lebih baik
        insight += `<div class="bg-gray-50 p-3 rounded-lg border border-gray-200">`;
        insight += `<p class="text-gray-700 mb-2">Total perjalanan: <span class="font-medium text-indigo-600">${totalTrips}</span></p>`;
        
        // Informasi destinasi teratas
        insight += `<div class="bg-amber-50 p-2 rounded border border-amber-200 mt-2">`;
        insight += `<p class="text-amber-700 font-medium">Destinasi Terpopuler: ${topDestination.tujuan}</p>`;
        insight += `<div class="grid grid-cols-2 gap-2 text-sm mt-1">`;
        insight += `<div><span class="text-gray-600">Jumlah kunjungan:</span> <span class="font-medium">${topDestination.count}</span></div>`;
        insight += `<div><span class="text-gray-600">Persentase dari total:</span> <span class="font-medium">${((topDestination.count / totalTrips) * 100).toFixed(1)}%</span></div>`;
        insight += `</div>`; // End grid
        insight += `</div>`; // End top destination box
        
        // Tabel perbandingan destinasi
        insight += `<div class="overflow-x-auto mt-3">`;
        insight += `<table class="min-w-full text-sm">`;
        insight += `<thead><tr class="bg-gray-100">`;
        insight += `<th class="px-2 py-1 text-left">Destinasi</th>`;
        insight += `<th class="px-2 py-1 text-right">Jumlah</th>`;
        insight += `<th class="px-2 py-1 text-right">Persentase</th>`;
        insight += `</tr></thead><tbody>`;
        
        destinationData.slice(0, 5).forEach(dest => {
            const percentage = ((dest.count / totalTrips) * 100).toFixed(1);
            
            insight += `<tr class="border-t border-gray-200">`;
            insight += `<td class="px-2 py-1">${dest.tujuan}</td>`;
            insight += `<td class="px-2 py-1 text-right">${dest.count}</td>`;
            insight += `<td class="px-2 py-1 text-right">${percentage}%</td>`;
            insight += `</tr>`;
        });
        
        insight += `</tbody></table>`;
        insight += `</div>`; // End overflow-x-auto
        
        // Analisis distribusi destinasi
        insight += `<div class="mt-3 pt-2 border-t border-gray-200">`;
        insight += `<p class="text-sm text-gray-600">`;
        insight += `${topDestination.tujuan} merupakan destinasi paling populer dengan ${topDestination.count} kunjungan `;
        insight += `(${((topDestination.count / totalTrips) * 100).toFixed(1)}% dari total perjalanan). `;
        
        if (destinationData.length > 1) {
            const secondDest = destinationData[1];
            const difference = topDestination.count - secondDest.count;
            const percentDiff = ((difference / secondDest.count) * 100).toFixed(1);
            
            insight += `Jumlah ini ${percentDiff}% lebih tinggi dibandingkan ${secondDest.tujuan} `;
            insight += `yang berada di posisi kedua dengan ${secondDest.count} kunjungan.`;
        }
        
        insight += `</p>`;
        insight += `</div>`; // End analisis distribusi
        
        insight += `</div>`; // End info box
        insight += `</div>`; // End space-y-3
        
        const insightElement = document.getElementById('insight-destination');
        if (insightElement) {
            insightElement.innerHTML = insight;
        }
    }
    
    // Helper method untuk format currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
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