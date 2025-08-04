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

class LaporanDetailExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Ringkasan' => new RingkasanDetailSheet(),
            'Detail SPPD' => new DetailSPPDSheet(),
        ];
    }
}

class RingkasanDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        $data = $this->getDetailData();
        return collect([
            ['KOMISI PEMILIHAN UMUM KABUPATEN CIREBON'],
            ['LAPORAN DETAIL SPPD'],
            ['Periode: ' . now()->format('d F Y')],
            [''],
            ['RINGKASAN DETAIL SPPD'],
            ['Kategori', 'Jumlah', 'Persentase', 'Keterangan'],
            ['Total SPPD', $data['totalSPPD'], '100%', 'Semua SPPD dalam sistem'],
            ['SPPD Disetujui', $data['completedSPPD'], $data['totalSPPD'] > 0 ? number_format(($data['completedSPPD'] / $data['totalSPPD']) * 100, 1) . '%' : '0%', 'SPPD yang telah disetujui'],
            ['SPPD Dalam Review', $data['inReviewSPPD'], $data['totalSPPD'] > 0 ? number_format(($data['inReviewSPPD'] / $data['totalSPPD']) * 100, 1) . '%' : '0%', 'SPPD dalam proses review'],
            ['SPPD Ditolak', $data['rejectedSPPD'], $data['totalSPPD'] > 0 ? number_format(($data['rejectedSPPD'] / $data['totalSPPD']) * 100, 1) . '%' : '0%', 'SPPD yang ditolak'],
            ['SPPD Revisi', $data['revisionSPPD'], $data['totalSPPD'] > 0 ? number_format(($data['revisionSPPD'] / $data['totalSPPD']) * 100, 1) . '%' : '0%', 'SPPD yang sedang direvisi'],
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
            10 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
            11 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getStyle('A1:D11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $event->sheet->mergeCells('A1:D1');
                $event->sheet->mergeCells('A2:D2');
                $event->sheet->mergeCells('A3:D3');
                $event->sheet->mergeCells('A5:D5');
            },
        ];
    }

    private function getDetailData()
    {
        $sppdList = TravelRequest::with(['user', 'approvals.approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'sppdList' => $sppdList,
            'totalSPPD' => $sppdList->count(),
            'completedSPPD' => $sppdList->where('status', 'completed')->count(),
            'inReviewSPPD' => $sppdList->where('status', 'in_review')->count(),
            'rejectedSPPD' => $sppdList->where('status', 'rejected')->count(),
            'revisionSPPD' => $sppdList->where('status', 'revision')->count()
        ];
    }
}

class DetailSPPDSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return TravelRequest::with(['user', 'approvals.approver'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode SPPD',
            'Nama Pengaju',
            'Role',
            'Tujuan',
            'Keperluan',
            'Tanggal Berangkat',
            'Tanggal Kembali',
            'Durasi',
            'Total Biaya',
            'Status',
            'Tanggal Dibuat'
        ];
    }

    public function map($sppd): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $sppd->kode_sppd,
            $sppd->user->name ?? 'N/A',
            ucfirst($sppd->user->role ?? 'N/A'),
            $sppd->tujuan,
            $sppd->keperluan,
            $sppd->tanggal_berangkat ? Carbon::parse($sppd->tanggal_berangkat)->format('d/m/Y') : '',
            $sppd->tanggal_kembali ? Carbon::parse($sppd->tanggal_kembali)->format('d/m/Y') : '',
            $sppd->lama_perjalanan,
            'Rp ' . number_format($sppd->total_biaya, 0, ',', '.'),
            $this->getStatusLabel($sppd->status),
            $sppd->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6366F1']]],
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