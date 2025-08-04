<?php

namespace App\Exports;

use App\Models\Document;
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

class LaporanDokumenExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Ringkasan' => new RingkasanDokumenSheet(),
            'Dokumen Terverifikasi' => new DokumenTerverifikasiSheet(),
            'Dokumen Belum Verifikasi' => new DokumenBelumVerifikasiSheet(),
        ];
    }
}

class RingkasanDokumenSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        $data = $this->getDocumentData();
        return collect([
            ['KOMISI PEMILIHAN UMUM KABUPATEN CIREBON'],
            ['LAPORAN DOKUMEN SPPD'],
            ['Periode: ' . now()->format('d F Y')],
            [''],
            ['RINGKASAN DOKUMEN'],
            ['Kategori', 'Jumlah', 'Persentase', 'Keterangan'],
            ['Total Dokumen', $data['totalDocuments'], '100%', 'Semua dokumen dalam sistem'],
            ['Dokumen Terverifikasi', $data['verifiedDocuments'], $data['totalDocuments'] > 0 ? number_format($data['verificationRate'], 1) . '%' : '0%', 'Dokumen yang telah diverifikasi'],
            ['Dokumen Belum Verifikasi', $data['unverifiedDocuments'], $data['totalDocuments'] > 0 ? number_format(100 - $data['verificationRate'], 1) . '%' : '0%', 'Dokumen yang belum diverifikasi'],
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

    private function getDocumentData()
    {
        $documents = Document::with(['travelRequest', 'uploader'])->orderBy('created_at', 'desc')->get();
        $totalDocuments = $documents->count();
        $verifiedDocuments = $documents->where('is_verified', true)->count();
        $unverifiedDocuments = $totalDocuments - $verifiedDocuments;
        $verificationRate = $totalDocuments > 0 ? ($verifiedDocuments / $totalDocuments) * 100 : 0;

        return compact('totalDocuments', 'verifiedDocuments', 'unverifiedDocuments', 'verificationRate');
    }
}

class DokumenTerverifikasiSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return Document::with(['travelRequest', 'uploader'])
            ->where('is_verified', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Dokumen',
            'SPPD',
            'Pengaju',
            'Jenis Dokumen',
            'Status',
            'Tanggal Upload',
            'Ukuran File'
        ];
    }

    public function map($document): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $document->original_filename,
            $document->travelRequest->kode_sppd ?? 'N/A',
            $document->uploader->name ?? 'N/A',
            $document->document_type,
            'Terverifikasi',
            $document->created_at->format('d/m/Y H:i'),
            $document->file_size ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']]],
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

class DokumenBelumVerifikasiSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return Document::with(['travelRequest', 'uploader'])
            ->where('is_verified', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Dokumen',
            'SPPD',
            'Pengaju',
            'Jenis Dokumen',
            'Status',
            'Tanggal Upload',
            'Ukuran File'
        ];
    }

    public function map($document): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $document->original_filename,
            $document->travelRequest->kode_sppd ?? 'N/A',
            $document->uploader->name ?? 'N/A',
            $document->document_type,
            'Belum Verifikasi',
            $document->created_at->format('d/m/Y H:i'),
            $document->file_size ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']]],
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