<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelRequest;
use App\Models\User;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanRekapitulasiExport;
use App\Exports\LaporanDokumenExport;
use App\Exports\LaporanAnggaranExport;
use App\Exports\LaporanDetailExport;
use App\Exports\LaporanPenggunaExport;

class LaporanController extends Controller
{
    // Valid report types for export functions
    private const VALID_REPORT_TYPES = [
        'rekapitulasi',
        'dokumen', 
        'anggaran',
        'detail',
        'pengguna'
    ];

    // Status constants to fix inconsistency
    private const STATUS_COMPLETED = 'completed';

    /**
     * Display the report list page
     */
    public function daftar()
    {
        return view('laporan.laporan-daftar');
    }

    /**
     * Export Excel report with proper validation
     */
    public function exportExcel($jenis = null)
    {
        try {
            // Validate input
            $validator = Validator::make(['jenis' => $jenis], [
                'jenis' => 'required|string|in:' . implode(',', self::VALID_REPORT_TYPES)
            ]);

            if ($validator->fails()) {
                Log::warning('Invalid Excel export request', [
                    'jenis' => $jenis,
                    'errors' => $validator->errors()->toArray()
                ]);
                return redirect()->route('laporan.daftar')
                    ->with('error', 'Jenis laporan tidak valid atau tidak ditentukan');
            }

            $filename = 'Laporan_' . ucfirst($jenis) . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Use Excel export for all report types
            switch ($jenis) {
                case 'rekapitulasi':
                    return Excel::download(new LaporanRekapitulasiExport(), $filename);
                case 'dokumen':
                    return Excel::download(new LaporanDokumenExport(), $filename);
                case 'anggaran':
                    return Excel::download(new LaporanAnggaranExport(), $filename);
                case 'detail':
                    return Excel::download(new LaporanDetailExport(), $filename);
                case 'pengguna':
                    return Excel::download(new LaporanPenggunaExport(), $filename);
                default:
                    return redirect()->route('laporan.daftar')
                        ->with('error', 'Jenis laporan tidak valid');
            }
        } catch (\Exception $e) {
            Log::error('Export Excel Error', [
                'jenis' => $jenis,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return error response instead of redirect for better debugging
            return response()->json([
                'error' => 'Terjadi kesalahan saat export Excel: ' . $e->getMessage(),
                'details' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Export PDF report with proper validation
     */
    public function exportPdf($jenis = null)
    {
        try {
            // Validate input
            $validator = Validator::make(['jenis' => $jenis], [
                'jenis' => 'required|string|in:' . implode(',', self::VALID_REPORT_TYPES)
            ]);

            if ($validator->fails()) {
                Log::warning('Invalid PDF export request', [
                    'jenis' => $jenis,
                    'errors' => $validator->errors()->toArray()
                ]);
                return redirect()->route('laporan.daftar')
                    ->with('error', 'Jenis laporan tidak valid atau tidak ditentukan');
            }

            $filename = 'Laporan_' . ucfirst($jenis) . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';

            // Common variables for all PDF exports
            $commonData = [
                'catatan' => null,
                'penanggung_jawab' => 'Sekretaris KPU Kabupaten Cirebon',
                'jabatan' => 'Sekretaris',
                'tanggal_laporan' => now()->toDateString(),
            ];

            switch ($jenis) {
                case 'rekapitulasi':
                    $data = array_merge($this->getAnalyticsData(['periode' => '1bulan']), $commonData);
                    $pdf = Pdf::loadView('laporan.export_pdf', $data);
                    return $pdf->download($filename);
                case 'dokumen':
                    $data = array_merge($this->getDocumentData(), $commonData);
                    $pdf = Pdf::loadView('laporan.export_dokumen_pdf', $data);
                    return $pdf->download($filename);
                case 'anggaran':
                    $data = array_merge($this->getBudgetData(), $commonData);
                    $pdf = Pdf::loadView('laporan.export_anggaran_pdf', $data);
                    return $pdf->download($filename);
                case 'detail':
                    $data = array_merge($this->getDetailData(), $commonData);
                    $pdf = Pdf::loadView('laporan.export_detail_pdf', $data);
                    return $pdf->download($filename);
                case 'pengguna':
                    $data = array_merge($this->getUserData(), $commonData);
                    $pdf = Pdf::loadView('laporan.export_pengguna_pdf', $data);
                    return $pdf->download($filename);
                default:
                    return redirect()->route('laporan.daftar')
                        ->with('error', 'Jenis laporan tidak valid');
            }
        } catch (\Exception $e) {
            Log::error('Export PDF Error', [
                'jenis' => $jenis,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('laporan.daftar')
                ->with('error', 'Terjadi kesalahan saat export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Get analytics data with caching and optimized queries
     */
    private function getAnalyticsData($filter = [])
    {
        // Realtime data without cache for real-time updates
        $startDate = null;
        $endDate = null;
        $now = now();
            
            // Determine date range based on filter
        if (($filter['periode'] ?? '1bulan') === '1bulan') {
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif (($filter['periode'] ?? '') === '3bulan') {
            $startDate = $now->copy()->subMonths(2)->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif (($filter['periode'] ?? '') === '6bulan') {
            $startDate = $now->copy()->subMonths(5)->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif (($filter['periode'] ?? '') === '1tahun') {
            $startDate = $now->copy()->startOfYear();
            $endDate = $now->copy()->endOfYear();
        }

            Log::info('LAPORAN DEBUG: periode=' . ($filter['periode'] ?? '-') . 
                     ' | startDate=' . ($startDate ? $startDate->toDateString() : '-') . 
                     ' | endDate=' . ($endDate ? $endDate->toDateString() : '-'));

            // Base query with date filter
        $travelQuery = TravelRequest::query();
        if ($startDate && $endDate) {
            $travelQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Check if there's data in the filtered period
        $filteredCount = (clone $travelQuery)->count();
        
        // If no data in filtered period, get all data instead
        if ($filteredCount === 0) {
            Log::info('LAPORAN DEBUG: No data in filtered period, using all data instead');
            $travelQuery = TravelRequest::query(); // Reset to get all data
            $startDate = null;
            $endDate = null;
        }

            Log::info('LAPORAN DEBUG: SQL=' . $travelQuery->toSql() . 
                     ' | bindings=' . json_encode($travelQuery->getBindings()));

            // Main statistics with FIXED status consistency
        $totalSPPD = (clone $travelQuery)->count();
            $totalApproved = (clone $travelQuery)->where('status', self::STATUS_COMPLETED)->count();
        $totalRejected = (clone $travelQuery)->where('status', 'rejected')->count();
        $totalInReview = (clone $travelQuery)->where('status', 'in_review')->count();
        $totalRevision = (clone $travelQuery)->where('status', 'revision')->count();

            Log::info('LAPORAN DEBUG: totalSPPD=' . $totalSPPD . 
                     ' | totalApproved=' . $totalApproved . 
                     ' | totalRejected=' . $totalRejected . 
                     ' | totalInReview=' . $totalInReview . 
                     ' | totalRevision=' . $totalRevision);

            // Financial statistics with FIXED status consistency
            $totalBudget = (clone $travelQuery)->where('status', self::STATUS_COMPLETED)->sum('total_biaya');
            $avgBiaya = (clone $travelQuery)->where('status', self::STATUS_COMPLETED)->avg('total_biaya');
            $maxBiaya = (clone $travelQuery)->where('status', self::STATUS_COMPLETED)->max('total_biaya');
            $minBiaya = (clone $travelQuery)->where('status', self::STATUS_COMPLETED)
                ->where('total_biaya', '>', 0)->min('total_biaya');

            // Top destinations
        $topDestinations = (clone $travelQuery)
            ->select('tujuan', DB::raw('count(*) as total'))
            ->groupBy('tujuan')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Monthly trends (12 months) - Use all data for trends
        $months = [];
        $monthlyInReview = [];
        $monthlyApproved = [];
        $monthlyBudget = [];
            
        for ($i = 11; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $months[] = $month->format('M Y');
                
            $inReview = TravelRequest::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthlyInReview[] = $inReview;
                
                // FIXED: Use consistent status 'completed' instead of 'approved'
                $approved = TravelRequest::where('status', self::STATUS_COMPLETED)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthlyApproved[] = $approved;
                
                // FIXED: Use consistent status 'completed' instead of 'approved'
                $budget = TravelRequest::where('status', self::STATUS_COMPLETED)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_biaya');
            $monthlyBudget[] = $budget;
        }

            // Participant statistics
        $pesertaStats = DB::table('users')
            ->select('users.id', 'users.name', 'users.role',
                DB::raw('COUNT(DISTINCT travel_requests.id) as total_sppd'),
                    DB::raw("SUM(CASE WHEN travel_requests.status = '" . self::STATUS_COMPLETED . "' THEN 1 ELSE 0 END) as approved_count"),
                DB::raw("SUM(CASE WHEN travel_requests.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count"),
                DB::raw("SUM(CASE WHEN travel_requests.status = 'revision' THEN 1 ELSE 0 END) as revision_count"),
                DB::raw("SUM(CASE WHEN travel_requests.status = 'in_review' THEN 1 ELSE 0 END) as review_count"),
                    DB::raw("SUM(CASE WHEN travel_requests.status = '" . self::STATUS_COMPLETED . "' THEN travel_requests.total_biaya ELSE 0 END) as total_budget"))
            ->join('travel_request_participants', 'users.id', '=', 'travel_request_participants.user_id')
            ->join('travel_requests', 'travel_requests.id', '=', 'travel_request_participants.travel_request_id')
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                $q->whereBetween('travel_requests.created_at', [$startDate, $endDate]);
            })
            ->groupBy('users.id', 'users.name', 'users.role')
            ->havingRaw('COUNT(DISTINCT travel_requests.id) > 0')
            ->orderByRaw('COUNT(DISTINCT travel_requests.id) DESC')
            ->limit(20)
            ->get();

            // Status distribution
        $statusDistribution = [
            'completed' => $totalApproved,
            'in_review' => $totalInReview,
            'rejected' => $totalRejected,
            'revision' => $totalRevision,
        ];

            // Quarterly analysis
        $quarterlyData = [];
        for ($q = 1; $q <= 4; $q++) {
            $startMonth = ($q - 1) * 3 + 1;
            $endMonth = $q * 3;
            $quarterStart = Carbon::create($now->year, $startMonth, 1)->startOfMonth();
            $quarterEnd = Carbon::create($now->year, $endMonth, 1)->endOfMonth();
                
            if ($startDate && $endDate) {
                if ($quarterEnd < $startDate || $quarterStart > $endDate) {
                    $quarterlyData[] = [
                        'quarter' => 'Q' . $q,
                        'total' => 0,
                        'approved' => 0,
                        'budget' => 0
                    ];
                    continue;
                }
            }
                
            $quarterlyData[] = [
                'quarter' => 'Q' . $q,
                'total' => TravelRequest::whereYear('created_at', $now->year)
                    ->whereMonth('created_at', '>=', $startMonth)
                    ->whereMonth('created_at', '<=', $endMonth)
                    ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count(),
                    // FIXED: Use consistent status 'completed' instead of 'approved'
                    'approved' => TravelRequest::where('status', self::STATUS_COMPLETED)
                    ->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', '>=', $startMonth)
                    ->whereMonth('created_at', '<=', $endMonth)
                    ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count(),
                    // FIXED: Use consistent status 'completed' instead of 'approved'
                    'budget' => TravelRequest::where('status', self::STATUS_COMPLETED)
                    ->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', '>=', $startMonth)
                    ->whereMonth('created_at', '<=', $endMonth)
                    ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->sum('total_biaya')
            ];
        }

            // Document statistics
            $totalDocuments = Document::when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->count();
            
            $totalVerifiedDocuments = Document::when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->where('is_verified', true)->count();

            // User statistics
            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();

            // Alias for backward compatibility
        $totalCompleted = $totalApproved;

        return compact(
            'totalSPPD', 'totalApproved', 'totalRejected', 'totalInReview', 'totalRevision',
            'totalBudget', 'avgBiaya', 'maxBiaya', 'minBiaya',
            'topDestinations', 'months', 'monthlyInReview', 'monthlyApproved', 'monthlyBudget',
            'pesertaStats', 'statusDistribution', 'quarterlyData',
            'totalDocuments', 'totalVerifiedDocuments', 'totalUsers', 'activeUsers',
            'totalCompleted', 'filter', 'startDate', 'endDate'
        );
    }

    /**
     * Get document data with caching and optional memory optimization
     * 
     * @param int|null $limit Optional limit for large datasets to prevent memory issues
     *                        Use this when dealing with thousands of documents
     */
    private function getDocumentData($limit = null)
    {
        // Realtime data without cache for real-time updates
        $query = Document::with(['travelRequest', 'uploader'])
                ->orderBy('created_at', 'desc');
            
            // Add limit if specified (useful for large datasets)
            if ($limit) {
                $query->limit($limit);
            }
            
            $documents = $query->get();

            $totalDocuments = $documents->count();
            $verifiedDocuments = $documents->where('is_verified', true)->count();
            $unverifiedDocuments = $totalDocuments - $verifiedDocuments;

            return [
                'documents' => $documents,
                'totalDocuments' => $totalDocuments,
                'verifiedDocuments' => $verifiedDocuments,
                'unverifiedDocuments' => $unverifiedDocuments,
                'verificationRate' => $totalDocuments > 0 ? ($verifiedDocuments / $totalDocuments) * 100 : 0
            ];
    }

    /**
     * Get budget data with caching and FIXED status consistency
     */
    private function getBudgetData()
    {
        // Realtime data without cache for real-time updates
            $currentYear = now()->year;
            $budgetData = [];

            // Monthly budget data with FIXED status consistency
            for ($month = 1; $month <= 12; $month++) {
                $monthData = TravelRequest::where('status', self::STATUS_COMPLETED)
                    ->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $month)
                    ->selectRaw('
                        COUNT(*) as total_sppd,
                        SUM(total_biaya) as total_budget,
                        AVG(total_biaya) as avg_budget,
                        SUM(uang_harian) as total_harian,
                        SUM(biaya_transport) as total_transport,
                        SUM(biaya_lainnya) as total_lainnya
                    ')
                    ->first();

                $budgetData[] = [
                    'month' => \Carbon\Carbon::create($currentYear, $month, 1)->format('F Y'),
                    'total_sppd' => $monthData->total_sppd ?? 0,
                    'total_budget' => $monthData->total_budget ?? 0,
                    'avg_budget' => $monthData->avg_budget ?? 0,
                    'total_harian' => $monthData->total_harian ?? 0,
                    'total_transport' => $monthData->total_transport ?? 0,
                    'total_lainnya' => $monthData->total_lainnya ?? 0
                ];
            }

            // Top activities by budget with FIXED status consistency
            $topActivities = TravelRequest::where('status', self::STATUS_COMPLETED)
                ->selectRaw('keperluan, COUNT(*) as total_sppd, SUM(total_biaya) as total_budget')
                ->groupBy('keperluan')
                ->orderBy('total_budget', 'desc')
                ->limit(10)
                ->get();

            return [
                'budgetData' => $budgetData,
                'topActivities' => $topActivities,
                'totalBudget' => TravelRequest::where('status', self::STATUS_COMPLETED)->sum('total_biaya'),
                'avgBudget' => TravelRequest::where('status', self::STATUS_COMPLETED)->avg('total_biaya'),
                'currentYear' => $currentYear
            ];
    }

    /**
     * Get detail data with caching
     */
    private function getDetailData()
    {
        // Realtime data without cache for real-time updates
            $sppdList = TravelRequest::with(['user', 'approvals.approver'])
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'sppdList' => $sppdList,
                'totalSPPD' => $sppdList->count(),
                'completedSPPD' => $sppdList->where('status', self::STATUS_COMPLETED)->count(),
                'inReviewSPPD' => $sppdList->where('status', 'in_review')->count(),
                'rejectedSPPD' => $sppdList->where('status', 'rejected')->count(),
                'revisionSPPD' => $sppdList->where('status', 'revision')->count()
            ];
    }

    /**
     * Get user data with caching and FIXED status consistency
     */
    private function getUserData()
    {
        // Realtime data without cache for real-time updates
            $userStats = User::leftJoin('travel_requests', 'users.id', '=', 'travel_requests.user_id')
                ->select('users.id', 'users.name', 'users.role', 'users.email',
                    DB::raw('COUNT(travel_requests.id) as total_sppd'),
                DB::raw('SUM(CASE WHEN travel_requests.status = \'completed\' THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN travel_requests.status = \'rejected\' THEN 1 ELSE 0 END) as rejected_count'),
                DB::raw('SUM(CASE WHEN travel_requests.status = \'in_review\' THEN 1 ELSE 0 END) as review_count'),
                DB::raw('SUM(CASE WHEN travel_requests.status = \'revision\' THEN 1 ELSE 0 END) as revision_count'),
                DB::raw('SUM(CASE WHEN travel_requests.status = \'completed\' THEN travel_requests.total_biaya ELSE 0 END) as total_budget'))
                ->groupBy('users.id', 'users.name', 'users.role', 'users.email')
                ->orderBy('total_sppd', 'desc')
                ->get();

            return [
                'userStats' => $userStats,
                'totalUsers' => User::count(),
                'activeUsers' => User::where('is_active', true)->count(),
                'usersWithSPPD' => $userStats->where('total_sppd', '>', 0)->count()
            ];
    }

    /**
     * Shared method to prepare common data for both index and ajax methods
     * This eliminates code duplication between index() and ajaxRekap()
     */
    private function prepareReportData(Request $request): array
    {
        $filter = [
            'periode' => $request->input('periode', '1bulan'),
        ];
        
        $catatan = $request->input('catatan');
        $penanggung_jawab = $request->input('penanggung_jawab', 'Sekretaris KPU Kabupaten Cirebon');
        $jabatan = $request->input('jabatan', 'Sekretaris');
        $tanggal_laporan = $request->input('tanggal_laporan', now()->toDateString());
        
        $data = $this->getAnalyticsData($filter);
        
        return array_merge($data, [
            'catatan' => $catatan,
            'penanggung_jawab' => $penanggung_jawab,
            'jabatan' => $jabatan,
            'tanggal_laporan' => $tanggal_laporan
        ]);
    }

    /**
     * Display the main report page
     */
    public function index(Request $request)
    {
        try {
            $data = $this->prepareReportData($request);
        return view('laporan.laporan-rekapitulasi', $data);
        } catch (\Exception $e) {
            Log::error('Error in report index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('laporan.daftar')
                ->with('error', 'Terjadi kesalahan saat memuat laporan: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint for report data
     */
    public function ajaxRekap(Request $request)
    {
        try {
            $data = $this->prepareReportData($request);
            return view('laporan.partials.rekap', $data)->render();
        } catch (\Exception $e) {
            Log::error('Error in ajax report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat data laporan'
            ], 500);
        }
    }

    /**
     * Get status label helper
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'in_review' => 'Dalam Review',
            'revision' => 'Revisi',
            'completed' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        return $labels[$status] ?? ucfirst($status);
    }
}
