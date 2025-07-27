<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelRequest;
use App\Models\User;
use App\Models\Approval;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\BudgetCalculationTrait;

class AnalyticsController extends Controller
{
    use BudgetCalculationTrait;
    
    // Constants untuk menghindari duplikasi
    private const ALLOWED_ROLES = ['admin', 'kasubbag', 'sekretaris', 'ppk'];
    private const ALLOWED_PERIODS = ['1', '3', '6', '12', '24', 'all'];
    private const ALLOWED_DETAIL_TYPES = ['monthly', 'status', 'department', 'approval', 'destination', 'utilization'];

    /**
     * Dashboard analitik utama
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Enhanced authorization check
        if (!$this->hasAnalyticsAccess($user)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke laporan analitik.');
        }

        // Validate and sanitize period
        $period = $this->validatePeriod($request->get('period', '12'));
        $startDate = $this->getStartDate($period);

        try {
            $data = $this->getAnalyticsData($startDate);
            $data['period'] = $period;

            return view('analytics.dashboard', $data);

        } catch (\Exception $e) {
            \Log::error('Analytics Error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'period' => $period
            ]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data analitik.');
        }
    }

    /**
     * Endpoint AJAX untuk data analytics (JSON)
     */
    public function data(Request $request)
    {
        try {
            $period = $this->validatePeriod($request->get('period', '12'));
            $startDate = $this->getStartDate($period);

            $data = $this->getAnalyticsData($startDate);
            $data['period'] = $period;

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Analytics Data Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'error' => 'Gagal memuat data analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint AJAX untuk detail analytics (modal interaktif)
     */
    public function detail(Request $request)
    {
        // Validate input parameters
        $validated = $request->validate([
            'type' => ['required', Rule::in(self::ALLOWED_DETAIL_TYPES)],
            'period' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:50',
            'approver' => 'nullable|string|max:100',
            'tujuan' => 'nullable|string|max:255'
        ]);

        try {
            $result = $this->getDetailData($validated);
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Analytics Detail Error: ' . $e->getMessage(), $validated);
            return response()->json([
                'error' => 'Gagal memuat detail data.'
            ], 500);
        }
    }

    /**
     * Check if user has analytics access
     */
    private function hasAnalyticsAccess($user): bool
    {
        $hasAccess = $user && in_array($user->role, self::ALLOWED_ROLES);
        
        \Log::info('Analytics Access Check', [
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->role : null,
            'allowed_roles' => self::ALLOWED_ROLES,
            'has_access' => $hasAccess
        ]);
        
        return $hasAccess;
    }

    /**
     * Validate and sanitize period parameter
     */
    private function validatePeriod($period): string
    {
        return in_array($period, self::ALLOWED_PERIODS) ? $period : '12';
    }

    /**
     * Get start date based on period
     */
    private function getStartDate($period): Carbon
    {
        if ($period === 'all') {
            return Carbon::parse('1970-01-01');
        }
        return now()->subMonths((int)$period);
    }

    /**
     * Get all analytics data
     */
    private function getAnalyticsData($startDate): array
    {
        return [
            'overview' => $this->getOverviewStats($startDate),
            'monthlyTrends' => $this->getMonthlyTrends($startDate),
            'userPerformance' => $this->getUserPerformanceRanking($startDate),
            'statusDistribution' => $this->getStatusDistribution($startDate),
            'departmentAnalysis' => $this->getDepartmentAnalysis($startDate),
            'budgetAnalysis' => $this->getBudgetAnalysis($startDate),
            'approvalPerformance' => $this->getApprovalPerformance($startDate),
            'trendingData' => $this->getTrendingData($startDate)
        ];
    }

    /**
     * Get detail data based on type
     */
    private function getDetailData(array $params): array
    {
        $result = [
            'title' => '',
            'columns' => [],
            'data' => []
        ];

        switch ($params['type']) {
            case 'monthly':
                return $this->getMonthlyDetail($params['period'] ?? '');
            case 'status':
                return $this->getStatusDetail($params['status'] ?? '');
            case 'department':
                return $this->getDepartmentDetail($params['department'] ?? '');
            case 'approval':
                return $this->getApprovalDetail($params['approver'] ?? '');
            case 'destination':
                return $this->getDestinationDetail($params['tujuan'] ?? '');
            case 'utilization':
                return $this->getUtilizationDetail();
            default:
                return $result;
        }
    }

    /**
     * Get monthly detail data
     */
    private function getMonthlyDetail(string $period): array
    {
        if (empty($period)) {
            return ['title' => 'Period tidak valid', 'columns' => [], 'data' => []];
        }

        // Use database-agnostic date formatting
        $data = TravelRequest::whereRaw("DATE_FORMAT(created_at, '%b %Y') = ?", [$period])
            ->with('user:id,name')
            ->get();

        return $this->formatDetailResponse(
            "Daftar SPPD Bulan " . e($period),
            ['ID', 'Nama', 'Tujuan', 'Status', 'Anggaran', 'Tanggal'],
            $data
        );
    }

    /**
     * Get status detail data
     */
    private function getStatusDetail(string $status): array
    {
        if (empty($status)) {
            return ['title' => 'Status tidak valid', 'columns' => [], 'data' => []];
        }

        $data = TravelRequest::where('status', $status)
            ->with('user:id,name')
            ->get();

        return $this->formatDetailResponse(
            "Daftar SPPD Status " . e($status),
            ['ID', 'Nama', 'Tujuan', 'Anggaran', 'Tanggal'],
            $data
        );
    }

    /**
     * Get department detail data
     */
    private function getDepartmentDetail(string $department): array
    {
        if (empty($department)) {
            return ['title' => 'Department tidak valid', 'columns' => [], 'data' => []];
        }

        $data = TravelRequest::whereHas('user', function($q) use ($department) {
                $q->where('role', strtolower($department));
            })
            ->with('user:id,name')
            ->get();

        return $this->formatDetailResponse(
            "Daftar SPPD Departemen " . e($department),
            ['ID', 'Nama', 'Tujuan', 'Status', 'Anggaran', 'Tanggal'],
            $data
        );
    }

    /**
     * Get approval detail data
     */
    private function getApprovalDetail(string $approver): array
    {
        if (empty($approver)) {
            return ['title' => 'Approver tidak valid', 'columns' => [], 'data' => []];
        }

        $data = Approval::whereHas('user', function($q) use ($approver) {
                $q->where('name', $approver);
            })
            ->with('user:id,name')
            ->get();

        return [
            'title' => "Daftar Approval oleh " . e($approver),
            'columns' => ['ID', 'Nama Approver', 'Status', 'Tanggal'],
            'data' => $data->map(function($d) {
                return [
                    $d->id,
                    $d->user->name ?? '-',
                    e($d->status ?? ''),
                    $d->created_at->format('d/m/Y')
                ];
            })
        ];
    }

    /**
     * Get destination detail data
     */
    private function getDestinationDetail(string $tujuan): array
    {
        if (empty($tujuan)) {
            return ['title' => 'Tujuan tidak valid', 'columns' => [], 'data' => []];
        }

        $data = TravelRequest::where('tujuan', $tujuan)
            ->with('user:id,name')
            ->get();

        return $this->formatDetailResponse(
            "Daftar SPPD ke " . e($tujuan),
            ['ID', 'Nama', 'Status', 'Anggaran', 'Tanggal'],
            $data
        );
    }

    /**
     * Get utilization detail data
     */
    private function getUtilizationDetail(): array
    {
        $data = TravelRequest::where('status', 'completed')
            ->with('user:id,name')
            ->get();

        return $this->formatDetailResponse(
            "Detail Utilisasi Anggaran",
            ['ID', 'Nama', 'Tujuan', 'Anggaran', 'Tanggal'],
            $data
        );
    }

    /**
     * Get overview statistics with optimized single query
     */
    private function getOverviewStats($startDate)
    {
        // Single optimized query instead of multiple clones
        $stats = TravelRequest::selectRaw("
            COUNT(*) as total_requests,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
            COUNT(CASE WHEN status = 'in_review' THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
            SUM(CASE WHEN status = 'completed' THEN (biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya) ELSE 0 END) as total_budget
        ")
        ->where('created_at', '>=', $startDate)
        ->first();

        $totalRequests = (int)$stats->total_requests;
        $completedCount = (int)$stats->completed_count;
        $pendingCount = (int)$stats->pending_count;
        $rejectedCount = (int)$stats->rejected_count;
        $totalBudget = (float)$stats->total_budget;

        $approvalRate = $totalRequests > 0 ? round(($completedCount / $totalRequests) * 100, 1) : 0;

        return [
            'total_sppd' => $completedCount,
            'total_budget' => $totalBudget,
            'approved_count' => $completedCount,
            'pending_count' => $pendingCount,
            'rejected_count' => $rejectedCount,
            'approval_rate' => $approvalRate,
            'avg_process_time' => $this->calculateAverageProcessingTime($startDate),
            'budget_utilization' => $this->getBudgetUtilization($startDate)
        ];
    }

    /**
     * Get monthly trends for SPPD count and budget
     */
    private function getMonthlyTrends($startDate)
    {
        return TravelRequest::select(
                DB::raw('EXTRACT(year FROM created_at) as year'),
                DB::raw('EXTRACT(month FROM created_at) as month'),
                DB::raw('COUNT(*) as sppd_count'),
                DB::raw('SUM(biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya) as total_budget')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($item) {
                $date = Carbon::create((int)$item->year, (int)$item->month, 1);
                return [
                    'period' => $date->format('M Y'),
                    'month_year' => $date->format('Y-m'),
                    'sppd_count' => (int)$item->sppd_count,
                    'total_budget' => (float)$item->total_budget,
                ];
            });
    }

    /**
     * Get user performance ranking
     */
    private function getUserPerformanceRanking($startDate)
    {
                    return TravelRequest::select(
                'users.name',
                'users.role',
                'users.email',
                DB::raw('COUNT(*) as total_requests'),
                DB::raw("COUNT(CASE WHEN travel_requests.status = 'completed' THEN 1 END) as approved_requests"),
                DB::raw("COUNT(CASE WHEN travel_requests.status = 'rejected' THEN 1 END) as rejected_requests"),
                DB::raw('SUM(biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya) as total_budget'),
                DB::raw('AVG(biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya) as avg_budget'),
                DB::raw('COUNT(CASE WHEN travel_requests.status = \'revision_minor\' THEN 1 END) as revision_count')
            )
            ->join('users', 'travel_requests.user_id', '=', 'users.id')
            ->where('travel_requests.created_at', '>=', $startDate)
            ->groupBy('users.id', 'users.name', 'users.role', 'users.email')
            ->orderBy('total_requests', 'desc')
            ->limit(20)
            ->get()
            ->map(function($item, $index) {
                $totalRequests = (int)$item->total_requests;
                $approvedRequests = (int)$item->approved_requests;
                
                $approvalRate = $totalRequests > 0 ? 
                    round(($approvedRequests / $totalRequests) * 100, 1) : 0;

                $efficiency = $this->calculateUserEfficiency($item);

                return [
                    'rank' => $index + 1,
                    'name' => $item->name,
                    'role' => ucfirst($item->role),
                    'email' => $item->email,
                    'total_requests' => $totalRequests,
                    'approved_requests' => $approvedRequests,
                    'rejected_requests' => (int)$item->rejected_requests,
                    'revision_count' => (int)$item->revision_count,
                    'total_budget' => (float)$item->total_budget,
                    'avg_budget' => (float)$item->avg_budget,
                    'approval_rate' => $approvalRate,
                    'efficiency_score' => $efficiency,
                    'performance_level' => $this->getPerformanceLevel($efficiency)
                ];
            });
    }

    /**
     * Calculate user efficiency score
     */
    private function calculateUserEfficiency($user): float
    {
        $totalRequests = (int)$user->total_requests;
        if ($totalRequests == 0) return 0;

        $approvalRate = ((int)$user->approved_requests / $totalRequests) * 100;
        $revisionPenalty = ((int)$user->revision_count / $totalRequests) * 10;
        $rejectionPenalty = ((int)$user->rejected_requests / $totalRequests) * 20;

        $efficiency = $approvalRate - $revisionPenalty - $rejectionPenalty;

        return max(0, min(100, round($efficiency, 1)));
    }

    /**
     * Get performance level based on efficiency score
     */
    private function getPerformanceLevel(float $efficiency): string
    {
        if ($efficiency >= 90) return 'Excellent';
        if ($efficiency >= 80) return 'Very Good';
        if ($efficiency >= 70) return 'Good';
        if ($efficiency >= 60) return 'Fair';
        return 'Needs Improvement';
    }

    /**
     * Get status distribution
     */
    private function getStatusDistribution($startDate)
    {
        return TravelRequest::select('status', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->status => (int)$item->count];
            });
    }

    /**
     * Get department analysis
     */
    private function getDepartmentAnalysis($startDate)
    {
        return TravelRequest::select(
                'users.role as department',
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya) as total_budget'),
                DB::raw('AVG(biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya) as avg_budget'),
                DB::raw("COUNT(CASE WHEN travel_requests.status = 'completed' THEN 1 END) as approved_count")
            )
            ->join('users', 'travel_requests.user_id', '=', 'users.id')
            ->where('travel_requests.created_at', '>=', $startDate)
            ->groupBy('users.role')
            ->orderBy('total_requests', 'desc')
            ->get()
            ->map(function($item) {
                $totalRequests = (int)$item->total_requests;
                $approvedCount = (int)$item->approved_count;
                
                return [
                    'department' => ucfirst($item->department),
                    'total_requests' => $totalRequests,
                    'total_budget' => (float)$item->total_budget,
                    'avg_budget' => (float)$item->avg_budget,
                    'approved_count' => $approvedCount,
                    'approval_rate' => $totalRequests > 0 ?
                        round(($approvedCount / $totalRequests) * 100, 1) : 0
                ];
            });
    }

    /**
     * Get budget analysis
     */
    private function getBudgetAnalysis($startDate)
    {
        $budgetRanges = [
            '0-1M' => [0, 1000000],
            '1M-5M' => [1000000, 5000000],
            '5M-10M' => [5000000, 10000000],
            '10M+' => [10000000, PHP_INT_MAX]
        ];

        $analysis = [];
        foreach ($budgetRanges as $range => $bounds) {
            $count = TravelRequest::where('created_at', '>=', $startDate)
                ->whereRaw('(biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya) >= ? AND (biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya) < ?', $bounds)
                ->count();

            $analysis[$range] = $count;
        }

        return $analysis;
    }

    /**
     * Get approval performance
     */
    private function getApprovalPerformance($startDate)
    {
        return Approval::select(
                'users.name as approver_name',
                'users.role as approver_role',
                DB::raw('COUNT(*) as total_approvals'),
                DB::raw("COUNT(CASE WHEN approvals.status = 'approved' THEN 1 END) as approved_count"),
                DB::raw("COUNT(CASE WHEN approvals.status = 'rejected' THEN 1 END) as rejected_count"),
                DB::raw('AVG(EXTRACT(EPOCH FROM (approvals.created_at - travel_requests.created_at))/86400) as avg_response_days')
            )
            ->join('users', 'approvals.approver_id', '=', 'users.id')
            ->join('travel_requests', 'approvals.travel_request_id', '=', 'travel_requests.id')
            ->where('approvals.created_at', '>=', $startDate)
            ->groupBy('users.id', 'users.name', 'users.role')
            ->orderBy('total_approvals', 'desc')
            ->get()
            ->map(function($item) {
                $totalApprovals = (int)$item->total_approvals;
                $approvedCount = (int)$item->approved_count;
                
                return [
                    'approver_name' => $item->approver_name,
                    'approver_role' => ucfirst($item->approver_role),
                    'total_approvals' => $totalApprovals,
                    'approved_count' => $approvedCount,
                    'rejected_count' => (int)$item->rejected_count,
                    'approval_rate' => $totalApprovals > 0 ?
                        round(($approvedCount / $totalApprovals) * 100, 1) : 0,
                    'avg_response_days' => round((float)$item->avg_response_days, 1)
                ];
            });
    }

    /**
     * Get trending data
     */
    private function getTrendingData($startDate)
    {
        // Most frequent destinations
        $destinations = TravelRequest::select('tujuan', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('tujuan')
            ->groupBy('tujuan')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Most common purposes  
        $purposes = TravelRequest::select('keperluan', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('keperluan')
            ->groupBy('keperluan')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return [
            'top_destinations' => $destinations,
            'common_purposes' => $purposes
        ];
    }

    /**
     * Calculate average processing time
     */
    private function calculateAverageProcessingTime($startDate): float
    {
        $completedRequests = TravelRequest::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->whereNotNull('updated_at')
            ->select('created_at', 'updated_at')
            ->get();

        if ($completedRequests->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($completedRequests as $request) {
            if ($request->updated_at && $request->created_at) {
                $days = Carbon::parse($request->created_at)->diffInDays(Carbon::parse($request->updated_at));
                $totalDays += $days;
                $count++;
            }
        }

        return $count > 0 ? round($totalDays / $count, 1) : 0;
    }

    /**
     * Format detail response to reduce code duplication
     */
    private function formatDetailResponse(string $title, array $columns, $data): array
    {
        return [
            'title' => $title,
            'columns' => $columns,
            'data' => $data->map([$this, 'mapTravelRequestData'])
        ];
    }

    /**
     * Map travel request data for consistent formatting
     */
    private function mapTravelRequestData($d): array
    {
        return [
            $d->id,
            $d->user->name ?? '-',
            e($d->tujuan),
            e($d->status ?? ''),
            'Rp ' . number_format($this->calculateTotalBudget($d), 0, ',', '.'),
            $d->created_at->format('d/m/Y')
        ];
    }

    /**
     * Get budget utilization
     */
    private function getBudgetUtilization($startDate): array
    {
        // Ambil alokasi anggaran dari tabel settings
        $setting = Setting::where('key', 'budget_allocation')->first();
        $totalAllocated = $setting ? (int)$setting->value : 0;

        $totalUsed = TravelRequest::where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->sum(DB::raw('biaya_transport + biaya_penginapan + uang_harian + biaya_lainnya'));

        $remaining = max(0, $totalAllocated - $totalUsed);
        $utilizationRate = $totalAllocated > 0 ? round(($totalUsed / $totalAllocated) * 100, 1) : 0;

        return [
            'allocated' => $totalAllocated,
            'used' => (float)$totalUsed,
            'remaining' => $remaining,
            'utilization_rate' => $utilizationRate
        ];
    }

    /**
     * Export analytics data
     */
    public function export(Request $request)
    {
        // Validate export parameters
        $request->validate([
            'format' => 'required|in:excel,pdf',
            'period' => ['required', Rule::in(self::ALLOWED_PERIODS)]
        ]);

        // Implementation for exporting analytics data to Excel/PDF
        return response()->json(['message' => 'Export functionality will be implemented']);
    }
}
