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
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;

class LaporanRekapitulasiExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Laporan Rekapitulasi' => new RingkasanSheet(),
        ];
    }
}

class RingkasanSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithDrawings
{
    public function collection()
    {
        $sppdData = TravelRequest::with(['user', 'participants'])->orderBy('created_at', 'desc')->get();
        
        $rows = [
            ['KOMISI PEMILIHAN UMUM KABUPATEN CIREBON'],
            ['LAPORAN REKAPITULASI SPPD'],
            ['Periode: ' . now()->format('d F Y')],
            [''],
            ['DATA SPPD PER BULAN'],
            ['Kode SPPD', 'Nama Peserta', 'Role', 'Tujuan', 'Keperluan', 'Tanggal Berangkat', 'Tanggal Kembali', 'Durasi (Hari)', 'Total Biaya', 'Status', 'Tanggal Dibuat', 'Transportasi', 'Tempat Menginap'],
        ];
        
        // Add SPPD data rows
        foreach($sppdData as $sppd) {
            $participantNames = $sppd->participants->pluck('name')->implode(', ');
            $participantRoles = $sppd->participants->pluck('role')->implode(', ');
            
            // Get status text
            $statusText = $this->getStatusText($sppd->status);
            
            $rows[] = [
                $sppd->kode_sppd,
                $participantNames ?: ($sppd->user->name ?? 'N/A'),
                $participantRoles ?: (ucfirst($sppd->user->role ?? 'N/A')),
                $sppd->tujuan,
                $sppd->keperluan,
                $sppd->tanggal_berangkat ? Carbon::parse($sppd->tanggal_berangkat)->format('d/m/Y') : '',
                $sppd->tanggal_kembali ? Carbon::parse($sppd->tanggal_kembali)->format('d/m/Y') : '',
                $sppd->lama_perjalanan,
                'Rp ' . number_format($sppd->total_biaya, 0, ',', '.'),
                $statusText,
                $sppd->created_at->format('d/m/Y H:i'),
                $sppd->transportasi,
                $sppd->tempat_menginap ?? '-'
            ];
        }
        
        // Calculate real-time summary from actual data
        $totalSPPD = $sppdData->count();
        $totalApproved = $sppdData->where('status', 'completed')->count();
        $totalRejected = $sppdData->where('status', 'rejected')->count();
        $totalInReview = $sppdData->where('status', 'in_review')->count();
        $totalRevision = $sppdData->where('status', 'revision')->count();
        $totalBudget = $sppdData->where('status', 'completed')->sum('total_biaya');
        $avgBiaya = $sppdData->where('status', 'completed')->avg('total_biaya');
        
        // Add summary at the bottom
        $rows[] = ['']; // Empty row
        $rows[] = ['RINGKASAN SPPD'];
        $rows[] = ['Total SPPD: ' . $totalSPPD . ' dokumen'];
        $rows[] = ['SPPD Disetujui: ' . $totalApproved . ' dokumen (' . ($totalSPPD > 0 ? number_format(($totalApproved / $totalSPPD) * 100, 1) : '0') . '%)'];
        $rows[] = ['SPPD Ditolak: ' . $totalRejected . ' dokumen (' . ($totalSPPD > 0 ? number_format(($totalRejected / $totalSPPD) * 100, 1) : '0') . '%)'];
        $rows[] = ['SPPD Review: ' . $totalInReview . ' dokumen (' . ($totalSPPD > 0 ? number_format(($totalInReview / $totalSPPD) * 100, 1) : '0') . '%)'];
        $rows[] = ['SPPD Revisi: ' . $totalRevision . ' dokumen (' . ($totalSPPD > 0 ? number_format(($totalRevision / $totalSPPD) * 100, 1) : '0') . '%)'];
        $rows[] = ['Total Anggaran: Rp ' . number_format($totalBudget, 0, ',', '.')];
        $rows[] = ['Rata-rata Biaya: Rp ' . number_format($avgBiaya ?? 0, 0, ',', '.')];
        
        return collect($rows);
    }

    public function headings(): array
    {
        return [];
    }

    public function map($row): array
    {
        return $row;
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo KPU');
        $drawing->setDescription('Logo Komisi Pemilihan Umum');
        $drawing->setPath(public_path('images/logo.png'));
        $drawing->setHeight(80);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(10);
        
        return [$drawing];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $highestRow = $event->sheet->getHighestRow();
                
                // Logo positioning
                $event->sheet->getRowDimension('1')->setRowHeight(80);
                $event->sheet->getRowDimension('2')->setRowHeight(30);
                $event->sheet->getRowDimension('3')->setRowHeight(25);
                $event->sheet->getRowDimension('4')->setRowHeight(15);
                $event->sheet->getRowDimension('5')->setRowHeight(30);
                
                // Header styling
                $event->sheet->getStyle('A1:M1')->getFont()->setSize(18)->setBold(true);
                $event->sheet->getStyle('A2:M2')->getFont()->setSize(16)->setBold(true);
                $event->sheet->getStyle('A3:M3')->getFont()->setSize(14)->setBold(true);
                $event->sheet->getStyle('A5:M5')->getFont()->setSize(16)->setBold(true);
                
                // Alignment for headers
                $event->sheet->getStyle('A1:M5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1:M5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                
                // Merge cells for headers (adjust for logo)
                $event->sheet->mergeCells('A1:M1');
                $event->sheet->mergeCells('A2:M2');
                $event->sheet->mergeCells('A3:M3');
                $event->sheet->mergeCells('A5:M5');
                
                // Adjust header text position to accommodate logo
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getAlignment()->setIndent(1); // Move text slightly right
                
                // Table header styling
                $event->sheet->getStyle('A6:M6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1F4E79');
                $event->sheet->getStyle('A6:M6')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                $event->sheet->getStyle('A6:M6')->getFont()->setBold(true);
                
                // Data rows styling
                $dataStartRow = 7;
                $dataEndRow = $highestRow - 8; // Exclude summary rows at bottom
                if ($dataEndRow >= $dataStartRow) {
                    $event->sheet->getStyle('A' . $dataStartRow . ':M' . $dataEndRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
                    
                                         // Color coding for different statuses (matching dashboard colors)
                     for($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                         $statusCell = $event->sheet->getCell('J' . $row)->getValue();
                         switch($statusCell) {
                             case 'Disetujui':
                                 $event->sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('10B981'); // Green
                                 $event->sheet->getStyle('J' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                                 break;
                             case 'Ditolak':
                                 $event->sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EF4444'); // Red
                                 $event->sheet->getStyle('J' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                                 break;
                             case 'Dalam Review':
                                 $event->sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F59E0B'); // Orange/Yellow
                                 $event->sheet->getStyle('J' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                                 break;
                             case 'Revisi':
                                 $event->sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('8B5CF6'); // Purple
                                 $event->sheet->getStyle('J' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                                 break;
                             case 'Menunggu':
                                 $event->sheet->getStyle('J' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6B7280'); // Gray
                                 $event->sheet->getStyle('J' . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF'));
                                 break;
                         }
                     }
                }
                
                // Summary section styling
                $summaryStartRow = $highestRow - 7;
                $event->sheet->getStyle('A' . $summaryStartRow . ':M' . $highestRow)->getFont()->setBold(true);
                $event->sheet->getStyle('A' . $summaryStartRow . ':M' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // Borders for data table
                if ($dataEndRow >= $dataStartRow) {
                    $event->sheet->getStyle('A6:M' . $dataEndRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                
                // Auto-size columns
                foreach(range('A', 'M') as $column) {
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function getStatusText($status)
    {
        switch($status) {
            case 'completed':
                return 'Disetujui';
            case 'rejected':
                return 'Ditolak';
            case 'in_review':
                return 'Dalam Review';
            case 'revision':
                return 'Revisi';
            case 'pending':
                return 'Menunggu';
            default:
                return ucfirst($status);
        }
    }


} 