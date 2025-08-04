<?php

namespace App\Exports;

use App\Models\User;
use App\Models\TravelRequest;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class LaporanPenggunaExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Ringkasan' => new RingkasanPenggunaSheet(),
            'Performa Pengguna' => new PerformaPenggunaSheet(),
        ];
    }
}

class RingkasanPenggunaSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        $data = $this->getUserData();
        return collect([
            ['KOMISI PEMILIHAN UMUM KABUPATEN CIREBON'],
            ['LAPORAN PENGGUNA SPPD'],
            ['Periode: ' . now()->format('d F Y')],
            [''],
            ['RINGKASAN PENGGUNA'],
            ['Kategori', 'Jumlah', 'Persentase', 'Keterangan'],
            ['Total Pengguna', $data['totalUsers'], '100%', 'Semua pengguna dalam sistem'],
            ['Pengguna Aktif', $data['activeUsers'], $data['totalUsers'] > 0 ? number_format(($data['activeUsers'] / $data['totalUsers']) * 100, 1) . '%' : '0%', 'Pengguna yang aktif'],
            ['Pengguna dengan SPPD', $data['usersWithSPPD'], $data['totalUsers'] > 0 ? number_format(($data['usersWithSPPD'] / $data['totalUsers']) * 100, 1) . '%' : '0%', 'Pengguna yang pernah membuat SPPD'],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function map($row): array
    {
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
            7 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
            8 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
            9 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:D9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->mergeCells('A2:D2');
                $event->sheet->mergeCells('A3:D3');
                $event->sheet->mergeCells('A5:D5');
            },
        ];
    }

    private function getUserData()
    {
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
}

class PerformaPenggunaSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return User::leftJoin('travel_requests', 'users.id', '=', 'travel_requests.user_id')
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
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama User',
            'Email',
            'Role',
            'Total SPPD',
            'SPPD Disetujui',
            'SPPD Ditolak',
            'SPPD Review',
            'SPPD Revisi',
            'Total Budget',
            'Success Rate',
            'Rata-rata per SPPD'
        ];
    }

    public function map($user): array
    {
        static $no = 0;
        $no++;

        $successRate = $user->total_sppd > 0 ? ($user->approved_count / $user->total_sppd) * 100 : 0;
        $avgPerSPPD = $user->approved_count > 0 ? $user->total_budget / $user->approved_count : 0;

        return [
            $no,
            $user->name,
            $user->email,
            ucfirst($user->role),
            $user->total_sppd,
            $user->approved_count,
            $user->rejected_count,
            $user->review_count,
            $user->revision_count,
            'Rp ' . number_format($user->total_budget, 0, ',', '.'),
            number_format($successRate, 1) . '%',
            'Rp ' . number_format($avgPerSPPD, 0, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EC4899']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:L' . ($event->sheet->getHighestRow()))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
} 