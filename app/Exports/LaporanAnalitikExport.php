<?php

namespace App\Exports;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Traits\BudgetCalculationTrait;

class LaporanAnalitikExport implements WithMultipleSheets
{
    use BudgetCalculationTrait;

    public function sheets(): array
    {
        return [
            'Ringkasan' => new RingkasanSheet(),
            'Data SPPD' => new DataSPPDSheet(),
            'Performa User' => new PerformaUserSheet(),
            'Tren Bulanan' => new TrenBulananSheet(),
        ];
    }
}

class RingkasanSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function collection()
    {
        // Get analytics data
        $totalSPPD = TravelRequest::count();
        $totalApproved = TravelRequest::where('status', 'completed')->count();
        $totalRejected = TravelRequest::where('status', 'rejected')->count();
        $totalInReview = TravelRequest::where('status', 'in_review')->count();
        $totalBudget = TravelRequest::where('status', 'completed')->sum('total_biaya');
        $avgBiaya = TravelRequest::where('status', 'completed')->avg('total_biaya');

        // Top destinations
        $topDestinations = TravelRequest::select('tujuan', DB::raw('count(*) as total'))
            ->groupBy('tujuan')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Create summary data
        return collect([
            (object)[
                'kategori' => 'Total SPPD',
                'nilai' => $totalSPPD,
                'keterangan' => 'Semua SPPD dalam sistem'
            ],
            (object)[
                'kategori' => 'SPPD Disetujui',
                'nilai' => $totalApproved,
                'keterangan' => 'SPPD yang telah disetujui'
            ],
            (object)[
                'kategori' => 'SPPD Ditolak',
                'nilai' => $totalRejected,
                'keterangan' => 'SPPD yang ditolak'
            ],
            (object)[
                'kategori' => 'SPPD Menunggu',
                'nilai' => $totalInReview,
                'keterangan' => 'SPPD dalam proses approval'
            ],
            (object)[
                'kategori' => 'Total Anggaran Disetujui',
                'nilai' => 'Rp ' . number_format($totalBudget, 0, ',', '.'),
                'keterangan' => 'Total biaya SPPD yang disetujui'
            ],
            (object)[
                'kategori' => 'Rata-rata Biaya SPPD',
                'nilai' => 'Rp ' . number_format($avgBiaya ?? 0, 0, ',', '.'),
                'keterangan' => 'Biaya rata-rata per SPPD'
            ],
        ])->concat(
            $topDestinations->map(function($dest, $index) {
                return (object)[
                    'kategori' => 'Top Destinasi #' . ($index + 1),
                    'nilai' => $dest->tujuan . ' (' . $dest->total . ' SPPD)',
                    'keterangan' => 'Destinasi populer perjalanan dinas'
                ];
            })
        );
    }

    public function headings(): array
    {
        return [
            'Kategori',
            'Nilai',
            'Keterangan'
        ];
    }

    public function map($row): array
    {
        return [
            $row->kategori,
            $row->nilai,
            $row->keterangan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }
}

class DataSPPDSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function collection()
    {
        return TravelRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode SPPD',
            'Nama Pengaju',
            'Role',
            'Tujuan',
            'Keperluan',
            'Tanggal Berangkat',
            'Tanggal Kembali',
            'Durasi (Hari)',
            'Total Biaya',
            'Status',
            'Tanggal Dibuat',
            'Transportasi',
            'Tempat Menginap'
        ];
    }

    public function map($sppd): array
    {
        return [
            $sppd->kode_sppd,
            $sppd->user->name ?? 'N/A',
            ucfirst($sppd->user->role ?? 'N/A'),
            $sppd->tujuan,
            $sppd->keperluan,
            $sppd->tanggal_berangkat ? \Carbon\Carbon::parse($sppd->tanggal_berangkat)->format('d/m/Y') : '',
            $sppd->tanggal_kembali ? \Carbon\Carbon::parse($sppd->tanggal_kembali)->format('d/m/Y') : '',
            $sppd->lama_perjalanan,
            'Rp ' . number_format($sppd->total_biaya, 0, ',', '.'),
            $this->getStatusLabel($sppd->status),
            $sppd->created_at->format('d/m/Y H:i'),
            $sppd->transportasi,
            $sppd->tempat_menginap ?? '-'
        ];
    }

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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Data SPPD';
    }
}

class PerformaUserSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function collection()
    {
        return User::leftJoin('travel_requests', 'users.id', '=', 'travel_requests.user_id')
            ->select('users.name', 'users.role',
                DB::raw('COUNT(travel_requests.id) as total_sppd'),
                DB::raw('SUM(CASE WHEN travel_requests.status = "completed" THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN travel_requests.status = "completed" THEN travel_requests.total_biaya ELSE 0 END) as total_budget'))
            ->groupBy('users.id', 'users.name', 'users.role')
            ->having('total_sppd', '>', 0)
            ->orderBy('total_sppd', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama User',
            'Role',
            'Total SPPD',
            'SPPD Disetujui',
            'Total Budget',
            'Success Rate (%)',
            'Rata-rata per SPPD'
        ];
    }

    public function map($user): array
    {
        $successRate = $user->total_sppd > 0 ? ($user->approved_count / $user->total_sppd) * 100 : 0;
        $avgPerSPPD = $user->approved_count > 0 ? $user->total_budget / $user->approved_count : 0;

        return [
            $user->name,
            ucfirst($user->role),
            $user->total_sppd,
            $user->approved_count,
            'Rp ' . number_format($user->total_budget, 0, ',', '.'),
            number_format($successRate, 1),
            'Rp ' . number_format($avgPerSPPD, 0, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Performa User';
    }
}

class TrenBulananSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function collection()
    {
        // Monthly trend data
        $months = [];
        $monthlyInReview = [];
        $monthlyCompleted = [];
        $monthlyBudget = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');

            $inReview = TravelRequest::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthlyInReview[] = $inReview;

            $completed = TravelRequest::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $monthlyCompleted[] = $completed;

            $budget = TravelRequest::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum($this->getBudgetCalculationRaw());
            $monthlyBudget[] = $budget;
        }

        return collect([
            (object)[
                'bulan' => $months,
                'in_review' => $monthlyInReview,
                'completed' => $monthlyCompleted,
                'budget' => $monthlyBudget,
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'SPPD Dalam Review',
            'SPPD Disetujui',
            'Anggaran Disetujui'
        ];
    }

    public function map($row): array
    {
        return [
            implode(', ', $row->bulan),
            implode(', ', $row->in_review),
            implode(', ', $row->completed),
            implode(', ', array_map(function($val) { return 'Rp ' . number_format($val, 0, ',', '.'); }, $row->budget))
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Tren Bulanan';
    }
}
