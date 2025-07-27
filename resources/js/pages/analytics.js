// resources/js/pages/analytics.js

// Pastikan Chart.js sudah tersedia di halaman

class AnalyticsPage {
    constructor() {
        this.charts = {};
        this.periodSelector = document.querySelector('select[name="period"]');
        this.init();
    }

    init() {
        if (this.periodSelector) {
            this.periodSelector.addEventListener('change', (e) => {
                this.fetchAndRender(e.target.value);
            });
        }
        // Initial load
        this.fetchAndRender(this.periodSelector ? this.periodSelector.value : '12');
        this.bindChartClicks();
        this.bindModalClose();
    }

    async fetchAndRender(period) {
        try {
            const res = await fetch(`/analytics/data?period=${period}`);
            const data = await res.json();
            if (data.error) throw new Error(data.error);
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
        this.renderMonthlyTrends(data.monthlyTrends);
        this.renderBudgetTrends(data.monthlyTrends);
        this.renderStatusDistribution(data.statusDistribution);
        this.renderDepartmentAnalysis(data.departmentAnalysis);
        this.renderApprovalPerformance(data.approvalPerformance);
        this.renderTopDestinations(data.trendingData.top_destinations);
        this.renderBudgetUtilization(data.overview && data.overview.budget_utilization ? data.overview.budget_utilization : null);
        this.renderInsights(data);
    }

    // 1. Grafik Tren Bulanan SPPD (Line Chart)
    renderMonthlyTrends(monthlyTrends) {
        const ctx = document.getElementById('monthlyTrendsChart');
        if (!ctx) return;
        const labels = (monthlyTrends || []).map(x => x.period);
        const sppd = (monthlyTrends || []).map(x => x.sppd_count);
        const approved = (monthlyTrends || []).map(x => x.approved_count || 0);
        if (this.charts.monthlyTrends) this.charts.monthlyTrends.destroy();
        this.charts.monthlyTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total SPPD',
                        data: sppd,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Disetujui',
                        data: approved,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } }
            }
        });
        if (!labels.length) this.showEmpty(ctx, 'Tidak ada data tren bulanan');
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
                        backgroundColor: 'rgba(59, 130, 246, 0.7)'
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
                    backgroundColor: 'rgba(59, 130, 246, 0.7)'
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
}

// Inisialisasi jika ada elemen analytics
if (document.getElementById('monthlyTrendsChart')) {
    window.analyticsPage = new AnalyticsPage();
} 