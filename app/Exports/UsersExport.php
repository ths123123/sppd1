<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'NIP',
            'Jabatan',
            'Role',
            'Unit Kerja',
            'Pangkat',
            'Golongan',
            'No. Telepon',
            'Status',
            'Login Terakhir',
            'Tanggal Terdaftar'
        ];
    }

    public function map($user): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $user->name,
            $user->email,
            $user->nip ?? '-',
            $user->jabatan ?? '-',
            ucfirst($user->role),
            $user->unit_kerja ?? '-',
            $user->pangkat ?? '-',
            $user->golongan ?? '-',
            $user->phone ?? '-',
            $user->is_active ? 'Aktif' : 'Tidak Aktif',
            $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Belum pernah login',
            $user->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $cellRange = 'A1:M1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1F2937'], // Gray-900 color
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Add borders to all cells
                $lastRow = $event->sheet->getDelegate()->getHighestRow();
                $allCells = 'A1:M' . $lastRow;
                $event->sheet->getDelegate()->getStyle($allCells)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);

                // Add title above the table
                $event->sheet->getDelegate()->insertNewRowBefore(1, 3);
                $event->sheet->getDelegate()->mergeCells('A1:M1');
                $event->sheet->getDelegate()->setCellValue('A1', 'DAFTAR USER SPPD KPU KABUPATEN CIREBON');
                $event->sheet->getDelegate()->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Add export info
                $event->sheet->getDelegate()->mergeCells('A2:M2');
                $event->sheet->getDelegate()->setCellValue('A2', 'Diekspor pada: ' . now()->format('d/m/Y H:i:s') . ' oleh: ' . auth()->user()->name);
                $event->sheet->getDelegate()->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
