<?php

namespace App\Exports;

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

class LaporanAnggaranExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Ringkasan' => new RingkasanAnggaranSheet(),
            'Anggaran Per Bulan' => new AnggaranPerBulanSheet(),
            'Top Kegiatan' => new TopKegiatanSheet(),
        ];
    }
}

class RingkasanAnggaranSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        $data = $this->getBudgetData();
        return collect([
            ['KOMISI PEMILIHAN UMUM KABUPATEN CIREBON'],
            ['LAPORAN ANGGARAN SPPD'],
            ['Periode: ' . now()->format('d F Y')],
            [''],
            ['RINGKASAN ANGGARAN'],
            ['Kategori', 'Jumlah', 'Keterangan'],
            ['Total Anggaran', 'Rp ' . number_format($data['totalBudget'], 0, ',', '.'), 'Total anggaran SPPD yang disetujui'],
            ['Rata-rata per SPPD', 'Rp ' . number_format($data['avgBudget'], 0, ',', '.'), 'Rata-rata anggaran per SPPD'],
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
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:C8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $event->sheet->mergeCells('A1:C1');
                $event->sheet->mergeCells('A2:C2');
                $event->sheet->mergeCells('A3:C3');
                $event->sheet->mergeCells('A5:C5');
            },
        ];
    }

    private function getBudgetData()
    {
        $totalBudget = TravelRequest::where('status', 'completed')->sum('total_biaya');
        $avgBudget = TravelRequest::where('status', 'completed')->avg('total_biaya');

        return compact('totalBudget', 'avgBudget');
    }
}

class AnggaranPerBulanSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        $currentYear = now()->year;
        $budgetData = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthData = TravelRequest::where('status', 'completed')
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
                'month' => Carbon::create($currentYear, $month, 1)->format('F Y'),
                'total_sppd' => $monthData->total_sppd ?? 0,
                'total_budget' => $monthData->total_budget ?? 0,
                'avg_budget' => $monthData->avg_budget ?? 0,
                'total_harian' => $monthData->total_harian ?? 0,
                'total_transport' => $monthData->total_transport ?? 0,
                'total_lainnya' => $monthData->total_lainnya ?? 0
            ];
        }

        return collect($budgetData);
    }

    public function headings(): array
    {
        return [
            'No',
            'Bulan',
            'Jumlah SPPD',
            'Total Anggaran',
            'Rata-rata per SPPD',
            'Uang Harian',
            'Biaya Transportasi',
            'Biaya Lainnya'
        ];
    }

    public function map($monthData): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $monthData['month'],
            $monthData['total_sppd'],
            'Rp ' . number_format($monthData['total_budget'], 0, ',', '.'),
            'Rp ' . number_format($monthData['avg_budget'], 0, ',', '.'),
            'Rp ' . number_format($monthData['total_harian'], 0, ',', '.'),
            'Rp ' . number_format($monthData['total_transport'], 0, ',', '.'),
            'Rp ' . number_format($monthData['total_lainnya'], 0, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:H' . ($event->sheet->getHighestRow()))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}

class TopKegiatanSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return TravelRequest::where('status', 'completed')
            ->selectRaw('keperluan, COUNT(*) as total_sppd, SUM(total_biaya) as total_budget')
            ->groupBy('keperluan')
            ->orderBy('total_budget', 'desc')
            ->limit(10)
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kegiatan',
            'Jumlah SPPD',
            'Total Anggaran',
            'Rata-rata per SPPD'
        ];
    }

    public function map($activity): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $activity->keperluan,
            $activity->total_sppd,
            'Rp ' . number_format($activity->total_budget, 0, ',', '.'),
            'Rp ' . number_format($activity->total_budget / $activity->total_sppd, 0, ',', '.')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B5CF6']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:E' . ($event->sheet->getHighestRow()))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
} 