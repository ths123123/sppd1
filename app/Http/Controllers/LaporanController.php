<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanAnalitikExport;
use PDF;

class LaporanController extends Controller
{
    public function exportPdf()
    {
        try {
            // Get the same data as index method
            $data = $this->getAnalyticsData();

            $pdf = PDF::loadView('laporan.export_pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            $filename = 'laporan-analitik-sppd-' . now()->format('Y-m-d-H-i-s') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Log::error('Export PDF error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat export PDF: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        try {
            $filename = 'laporan-analitik-sppd-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

            return Excel::download(new LaporanAnalitikExport(), $filename);
        } catch (\Exception $e) {
            \Log::error('Export Excel error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat export Excel: ' . $e->getMessage());
        }
    }

    private function getAnalyticsData($filter = [])
    {
        $startDate = null;
        $endDate = null;
        $now = now();
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
        \Log::info('LAPORAN DEBUG: periode=' . ($filter['periode'] ?? '-') . ' | startDate=' . ($startDate ? $startDate->toDateString() : '-') . ' | endDate=' . ($endDate ? $endDate->toDateString() : '-'));
        // Query builder dasar
        $travelQuery = TravelRequest::query();
        if ($startDate && $endDate) {
            $travelQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        \Log::info('LAPORAN DEBUG: SQL=' . $travelQuery->toSql() . ' | bindings=' . json_encode($travelQuery->getBindings()));
        // Statistik Utama
        $totalSPPD = (clone $travelQuery)->count();
        $totalApproved = (clone $travelQuery)->where('status', 'completed')->count();
        $totalRejected = (clone $travelQuery)->where('status', 'rejected')->count();
        $totalInReview = (clone $travelQuery)->where('status', 'in_review')->count();
        $totalRevision = (clone $travelQuery)->where('status', 'revision')->count();
        \Log::info('LAPORAN DEBUG: totalSPPD=' . $totalSPPD . ' | totalApproved=' . $totalApproved . ' | totalRejected=' . $totalRejected . ' | totalInReview=' . $totalInReview . ' | totalRevision=' . $totalRevision);
        // Statistik Financial
        $totalBudget = (clone $travelQuery)->where('status', 'completed')->sum('total_biaya');
        $avgBiaya = (clone $travelQuery)->where('status', 'completed')->avg('total_biaya');
        $maxBiaya = (clone $travelQuery)->where('status', 'completed')->max('total_biaya');
        $minBiaya = (clone $travelQuery)->where('status', 'completed')->where('total_biaya', '>', 0)->min('total_biaya');
        // Top Destinations
        $topDestinations = (clone $travelQuery)
            ->select('tujuan', DB::raw('count(*) as total'))
            ->groupBy('tujuan')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        // Tren Bulanan (12 bulan terakhir)
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
            $approved = TravelRequest::where('status', 'approved')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthlyApproved[] = $approved;
            $budget = TravelRequest::where('status', 'approved')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_biaya');
            $monthlyBudget[] = $budget;
        }
        // Rekap Peserta (bukan hanya pengaju)
        $pesertaStats = DB::table('users')
            ->select('users.id', 'users.name', 'users.role',
                DB::raw('COUNT(DISTINCT travel_requests.id) as total_sppd'),
                DB::raw("SUM(CASE WHEN travel_requests.status = 'completed' THEN 1 ELSE 0 END) as approved_count"),
                DB::raw("SUM(CASE WHEN travel_requests.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count"),
                DB::raw("SUM(CASE WHEN travel_requests.status = 'revision' THEN 1 ELSE 0 END) as revision_count"),
                DB::raw("SUM(CASE WHEN travel_requests.status = 'in_review' THEN 1 ELSE 0 END) as review_count"),
                DB::raw("SUM(CASE WHEN travel_requests.status = 'completed' THEN travel_requests.total_biaya ELSE 0 END) as total_budget"))
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
        // Status Distribution untuk Pie Chart dan Rekap
        $statusDistribution = [
            'completed' => $totalApproved,
            'in_review' => $totalInReview,
            'rejected' => $totalRejected,
            'revision' => $totalRevision,
        ];
        // Quarterly Analysis
        $quarterlyData = [];
        for ($q = 1; $q <= 4; $q++) {
            $startMonth = ($q - 1) * 3 + 1;
            $endMonth = $q * 3;
            $quarterStart = Carbon::create($now->year, $startMonth, 1)->startOfMonth();
            $quarterEnd = Carbon::create($now->year, $endMonth, 1)->endOfMonth();
            if ($startDate && $endDate) {
                // Batasi quarter pada range filter
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
                'approved' => TravelRequest::where('status', 'approved')
                    ->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', '>=', $startMonth)
                    ->whereMonth('created_at', '<=', $endMonth)
                    ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->count(),
                'budget' => TravelRequest::where('status', 'approved')
                    ->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', '>=', $startMonth)
                    ->whereMonth('created_at', '<=', $endMonth)
                    ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->sum('total_biaya')
            ];
        }
        // Statistik dokumen
        $totalDocuments = \App\Models\Document::when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->count();
        $totalVerifiedDocuments = \App\Models\Document::when($startDate && $endDate, function($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->where('is_verified', true)->count();
        // Statistik user
        $totalUsers = \App\Models\User::count();
        $activeUsers = \App\Models\User::where('is_active', true)->count();
        // Tambahkan alias agar view tidak error
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

    public function index(Request $request)
    {
        $filter = [
            'periode' => $request->input('periode', '1bulan'),
        ];
        $catatan = $request->input('catatan');
        $penanggung_jawab = $request->input('penanggung_jawab', 'Sekretaris KPU Kabupaten Cirebon');
        $jabatan = $request->input('jabatan', 'Sekretaris');
        $tanggal_laporan = $request->input('tanggal_laporan', now()->toDateString());
        $data = $this->getAnalyticsData($filter);
        $data['catatan'] = $catatan;
        $data['penanggung_jawab'] = $penanggung_jawab;
        $data['jabatan'] = $jabatan;
        $data['tanggal_laporan'] = $tanggal_laporan;
        return view('laporan.laporan-rekapitulasi', $data);
    }

    public function ajaxRekap(Request $request)
    {
        $filter = [
            'periode' => $request->input('periode', '1bulan'),
        ];
        $catatan = $request->input('catatan');
        $penanggung_jawab = $request->input('penanggung_jawab', 'Sekretaris KPU Kabupaten Cirebon');
        $jabatan = $request->input('jabatan', 'Sekretaris');
        $tanggal_laporan = $request->input('tanggal_laporan', now()->toDateString());
        $data = $this->getAnalyticsData($filter);
        $data['catatan'] = $catatan;
        $data['penanggung_jawab'] = $penanggung_jawab;
        $data['jabatan'] = $jabatan;
        $data['tanggal_laporan'] = $tanggal_laporan;
        return view('laporan.partials.rekap', $data)->render();
    }
}
