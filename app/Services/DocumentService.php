<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\TemplateDokumen;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DocumentService
{
    /**
     * Process document template based on type
     */
    public function processTemplate(TravelRequest $travelRequest, string $templateType): Response
    {
        return match($templateType) {
            'docx' => $this->processDocxTemplate($travelRequest),
            'pdf' => $this->processPdfTemplate($travelRequest),
            default => $this->generateFallbackPdf($travelRequest)
        };
    }

    /**
     * Process DOCX template
     */
    private function processDocxTemplate(TravelRequest $travelRequest): Response
    {
        $template = $this->getActiveTemplate();
        if (!$template) {
            throw new \Exception('Tidak ada template dokumen aktif.');
        }

        $templatePath = storage_path('app/public/' . $template->path_file);
        
        if (!$this->isValidTemplatePath($templatePath) || !file_exists($templatePath)) {
            throw new \Exception('Template file tidak ditemukan atau tidak valid.');
        }
        
        $participants = $this->getParticipants($travelRequest);
        $tempFiles = $this->generateDocxFiles($templatePath, $travelRequest, $participants);
        
        if (empty($tempFiles)) {
            throw new \Exception('Gagal membuat dokumen SPPD.');
        }
        
        return $this->createResponse($tempFiles, $travelRequest);
    }

    /**
     * Process PDF template
     */
    private function processPdfTemplate(TravelRequest $travelRequest): Response
    {
        $template = $this->getActiveTemplate();
        $templatePath = storage_path('app/public/' . $template->path_file);
        
        if (!$this->isValidTemplatePath($templatePath)) {
            throw new \Exception('Template path tidak valid.');
        }
        
        return response()->download($templatePath, 'SPPD-' . $travelRequest->kode_sppd . '.pdf');
    }

    /**
     * Generate fallback PDF
     */
    private function generateFallbackPdf(TravelRequest $travelRequest): Response
    {
        $statusLabels = [
            'in_review' => 'Sedang Review',
            'revision' => 'Revisi',
            'completed' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];
        
        $travelRequest->statusLabel = $statusLabels[$travelRequest->status] ?? $travelRequest->status;
        $travelRequest->total_biaya = $this->calculateTotalBudget($travelRequest);
        
        $pdf = \PDF::loadView('travel_requests.sppd_pdf', compact('travelRequest'));
        $pdf->setPaper('A4', 'portrait');
        
        $safeKode = $this->sanitizeFilename($travelRequest->kode_sppd);
        $filename = 'SPPD-' . $safeKode . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Get active template
     */
    private function getActiveTemplate(): ?TemplateDokumen
    {
        return TemplateDokumen::where('status_aktif', true)->first();
    }

    /**
     * Get participants for travel request
     */
    private function getParticipants(TravelRequest $travelRequest): \Illuminate\Support\Collection
    {
        $participants = $travelRequest->participants;
        
        if ($participants->isEmpty()) {
            $participants = collect([$travelRequest->user]);
        } else if (!$participants->contains('id', $travelRequest->user->id)) {
            $participants = $participants->prepend($travelRequest->user);
        }
        
        return $participants;
    }

    /**
     * Generate DOCX files for all participants
     */
    private function generateDocxFiles(string $templatePath, TravelRequest $travelRequest, \Illuminate\Support\Collection $participants): array
    {
        $tempFiles = [];
        $tempDir = storage_path('app/temp');
        
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        foreach ($participants as $peserta) {
            try {
                $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
                $variables = $this->getTemplateVariables($travelRequest, $peserta);
                
                foreach ($variables as $key => $value) {
                    $templateProcessor->setValue($key, $value);
                }
                
                $filename = 'SPPD-' . ($peserta->nip ?? $peserta->id) . '-' . now()->format('Y-m-d-H-i-s') . '.docx';
                $tempPath = $tempDir . '/' . $filename;
                $templateProcessor->saveAs($tempPath);
                
                if (file_exists($tempPath)) {
                    $tempFiles[] = $tempPath;
                }
            } catch (\Exception $e) {
                Log::error('Error processing template for participant ' . $peserta->id . ': ' . $e->getMessage());
                continue;
            }
        }
        
        return $tempFiles;
    }

    /**
     * Create response for generated files
     */
    private function createResponse(array $tempFiles, TravelRequest $travelRequest): Response
    {
        if (count($tempFiles) === 1) {
            $safeKode = $this->sanitizeFilename($travelRequest->kode_sppd);
            $filename = 'SPPD-' . $safeKode . '-' . now()->format('Y-m-d-H-i-s') . '.docx';
            
            return response()->download($tempFiles[0], $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        } else {
            return $this->createZipFromFiles($tempFiles, $travelRequest);
        }
    }

    /**
     * Create ZIP from multiple files
     */
    private function createZipFromFiles(array $tempFiles, TravelRequest $travelRequest): Response
    {
        $timestamp = now()->format('Y-m-d-H-i-s');
        $safeKode = $this->sanitizeFilename($travelRequest->kode_sppd);
        $zipFileName = "SPPD-Peserta-{$safeKode}-{$timestamp}.zip";
        $zipPath = storage_path("app/temp/{$zipFileName}");
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            throw new \Exception('Gagal membuat file ZIP.');
        }
        
        foreach ($tempFiles as $file) {
            if (file_exists($file)) {
                $zip->addFile($file, basename($file));
            }
        }
        
        $zip->close();
        
        // Clean up individual temp files
        foreach ($tempFiles as $file) {
            @unlink($file);
        }
        
        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            throw new \Exception('File ZIP kosong atau tidak dapat dibuat.');
        }
        
        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Get template variables for mail merge
     */
    private function getTemplateVariables(TravelRequest $travelRequest, $peserta): array
    {
        $ppk = \App\Models\User::where('role', 'ppk')->first();
        
        return [
            'lembar_ke' => 1,
            'kode_no' => $travelRequest->kode_sppd ?? '',
            'nomor' => $travelRequest->nomor_surat_tugas ?? '',
            'nama_ppk' => $ppk ? $ppk->name : '-',
            'nip_ppk' => $ppk ? $ppk->nip : '-',
            'nama_user' => $peserta->name,
            'nip_user' => $peserta->nip ?? '-',
            'jabatan_user' => $peserta->jabatan ?? '-',
            'pangkat' => $peserta->pangkat ?? '-',
            'golongan' => $peserta->golongan ?? '-',
            'instansi' => 'Komisi Pemilihan Umum Kabupaten Cirebon',
            'keperluan' => $travelRequest->keperluan,
            'transportasi' => $travelRequest->transportasi,
            'tempat_berangkat' => $travelRequest->tempat_berangkat,
            'tujuan' => $travelRequest->tujuan,
            'durasi' => $this->calculateDuration($travelRequest),
            'tanggal_berangkat' => $this->formatDate($travelRequest->tanggal_berangkat),
            'tanggal_kembali' => $this->formatDate($travelRequest->tanggal_kembali),
            'keterangan_pengajuan' => $travelRequest->catatan_pemohon ?? '-',
            'tanggal_surat_tugas' => $this->formatDate($travelRequest->tanggal_surat_tugas),
        ];
    }

    /**
     * Calculate duration in days
     */
    private function calculateDuration(TravelRequest $travelRequest): string
    {
        if ($travelRequest->tanggal_berangkat && $travelRequest->tanggal_kembali) {
            $days = Carbon::parse($travelRequest->tanggal_berangkat)
                ->diffInDays(Carbon::parse($travelRequest->tanggal_kembali)) + 1;
            return (string)$days;
        }
        return '-';
    }

    /**
     * Format date to Indonesian format
     */
    private function formatDate($date): string
    {
        if (!$date) return '-';
        return Carbon::parse($date)->translatedFormat('d F Y');
    }

    /**
     * Calculate total budget
     */
    private function calculateTotalBudget(TravelRequest $travelRequest): float
    {
        return ($travelRequest->biaya_transport ?? 0) + 
               ($travelRequest->biaya_penginapan ?? 0) + 
               ($travelRequest->uang_harian ?? 0) + 
               ($travelRequest->biaya_lainnya ?? 0);
    }

    /**
     * Sanitize filename
     */
    private function sanitizeFilename(string $filename): string
    {
        return str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $filename);
    }

    /**
     * Validate template path to prevent directory traversal
     */
    private function isValidTemplatePath(string $path): bool
    {
        $realPath = realpath($path);
        $storagePath = realpath(storage_path('app/public'));
        
        return $realPath && $storagePath && strpos($realPath, $storagePath) === 0;
    }
} 