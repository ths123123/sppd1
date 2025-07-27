<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TravelRequest;
use App\Models\TemplateDokumen;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

class ExportController extends Controller
{
    public function exportZip(Request $request, $id)
    {
        try {
            // Set memory limit dan timeout yang lebih tinggi
            ini_set('memory_limit', '1G');
            set_time_limit(600);
            
            // Clear any output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            $travelRequest = TravelRequest::with(['user', 'participants'])->findOrFail($id);
            
            // Check permissions
            $currentUser = Auth::user();
            $isPengaju = $travelRequest->user_id == $currentUser->id;
            $isPeserta = $travelRequest->participants->contains('id', $currentUser->id);
            $isPimpinan = in_array($currentUser->role, ['kasubbag','sekretaris','ppk','admin']);

            if ($travelRequest->status !== 'completed') {
                abort(403, 'SPPD hanya bisa diunduh jika sudah disetujui.');
            }

            if (!$isPengaju && !$isPeserta && !$isPimpinan) {
                abort(403, 'Unauthorized access.');
            }

            // Get template
            $template = TemplateDokumen::where('status_aktif', true)->first();
            if (!$template || $template->tipe_file !== 'docx') {
                abort(400, 'Template DOCX tidak tersedia.');
            }

            $templatePath = $this->normalizePath(storage_path('app/public/' . $template->path_file));
            if (!file_exists($templatePath)) {
                abort(500, 'Template file tidak ditemukan.');
            }

            // Generate DOCX files for all participants
            $files = $this->generateDocxFiles($travelRequest, $templatePath);
            
            if (empty($files)) {
                abort(500, 'Gagal membuat dokumen SPPD.');
            }

            // Create ZIP with robust error handling
            $zipResult = $this->createZipFile($files, $travelRequest);
            
            if (!$zipResult['success']) {
                throw new \Exception($zipResult['error']);
            }

            $zipPath = $zipResult['path'];
            $zipFileName = $zipResult['filename'];

            // Clean up individual DOCX files
            foreach ($files as $file) {
                if (isset($file['path']) && file_exists($file['path'])) {
                    @unlink($file['path']);
                }
            }

            \Log::info("ZIP export successful: {$zipFileName}");

            // Verify file exists and is readable
            if (!file_exists($zipPath) || !is_readable($zipPath)) {
                throw new \Exception('ZIP file not accessible: ' . $zipPath);
            }
            
            $fileSize = filesize($zipPath);
            \Log::info("Downloading ZIP: {$zipPath}, Size: {$fileSize} bytes");

            return response()->download($zipPath, $zipFileName, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'Accept-Ranges' => 'bytes'
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('ZIP Export Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            // Cleanup on error
            if (isset($files)) {
                foreach ($files as $file) {
                    if (isset($file['path']) && file_exists($file['path'])) {
                        @unlink($file['path']);
                    }
                }
            }

            if (isset($zipPath) && file_exists($zipPath)) {
                @unlink($zipPath);
            }

            return response()->json(['error' => 'Export failed: ' . $e->getMessage()], 500);
        }
    }




    private function createZipFile($files, $travelRequest)
    {
        try {
            // Create unique ZIP filename
            $timestamp = date('Y-m-d_H-i-s');
            $safeKode = $this->sanitizeFileName($travelRequest->kode_sppd);
            $zipFileName = "SPPD-Peserta-{$safeKode}-{$timestamp}.zip";
            
            // Normalize path consistently
            $zipPath = $this->normalizePath(storage_path('app/temp/' . $zipFileName));
            $zipDir = dirname($zipPath);
            
            // Ensure directory exists
            if (!file_exists($zipDir)) {
                if (!mkdir($zipDir, 0755, true)) {
                    throw new \Exception('Cannot create temp directory: ' . $zipDir);
                }
            }
            
            // Remove existing file if exists
            if (file_exists($zipPath)) {
                if (!unlink($zipPath)) {
                    throw new \Exception('Cannot remove existing ZIP file: ' . $zipPath);
                }
            }
            
            \Log::info('Creating ZIP file: ' . $zipPath);
            \Log::info('Files to add: ' . count($files));
            
            // Create ZIP with multiple attempts
            $zip = new ZipArchive();
            $attempts = 0;
            $maxAttempts = 3;
            
            do {
                $attempts++;
                \Log::info("ZIP creation attempt {$attempts}");
                
                $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                
                if ($result !== TRUE) {
                    \Log::error("ZIP open failed with code: {$result}");
                    
                    if ($attempts >= $maxAttempts) {
                        throw new \Exception("Failed to create ZIP file after {$maxAttempts} attempts. Error code: {$result}");
                    }
                    
                    // Wait before retry
                    sleep(1);
                    continue;
                }
                
                break;
            } while ($attempts < $maxAttempts);
            
            // Add files to ZIP
            $addedFiles = 0;
            foreach ($files as $file) {
                if (isset($file['path']) && file_exists($file['path'])) {
                    $fileSize = filesize($file['path']);
                    \Log::info("Adding file: {$file['name']} ({$fileSize} bytes)");
                    
                    $success = $zip->addFile($file['path'], $file['name']);
                    if ($success) {
                        $addedFiles++;
                        \Log::info("Successfully added: {$file['name']}");
                    } else {
                        \Log::error("Failed to add file: {$file['name']}");
                    }
                } else {
                    \Log::error("File not found: " . ($file['path'] ?? 'undefined'));
                }
            }
            
            \Log::info("Added {$addedFiles} files to ZIP");
            
            // Close ZIP with verification
            $closeResult = $zip->close();
            if (!$closeResult) {
                throw new \Exception('Failed to close ZIP file properly');
            }
            
            \Log::info("ZIP closed successfully");
            
            // Verify ZIP file integrity
            if (!file_exists($zipPath)) {
                throw new \Exception('ZIP file was not created');
            }
            
            $zipFileSize = filesize($zipPath);
            if ($zipFileSize === 0) {
                throw new \Exception('ZIP file is empty');
            }
            
            // Set proper file permissions
            chmod($zipPath, 0644);
            
            \Log::info("ZIP file created successfully. Size: {$zipFileSize} bytes");
            
            // Test ZIP integrity with more detailed check
            $testZip = new ZipArchive();
            $testResult = $testZip->open($zipPath, ZipArchive::CHECKCONS);
            if ($testResult !== TRUE) {
                \Log::error("ZIP integrity test failed with code: {$testResult}");
                throw new \Exception("Created ZIP file is corrupted. Test open failed with code: {$testResult}");
            }
            
            $fileCount = $testZip->numFiles;
            $testZip->close();
            
            \Log::info("ZIP integrity test passed. Contains {$fileCount} files");
            
            return [
                'success' => true,
                'path' => $zipPath,
                'filename' => $zipFileName,
                'size' => $zipFileSize,
                'files' => $fileCount
            ];
            
        } catch (\Exception $e) {
            \Log::error('ZIP creation error: ' . $e->getMessage());
            
            // Cleanup on error
            if (isset($zipPath) && file_exists($zipPath)) {
                @unlink($zipPath);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function generateDocxFiles($travelRequest, $templatePath)
    {
        $files = [];
        $participants = $travelRequest->participants;
        
        // Only use actual participants, don't add creator automatically
        if ($participants->isEmpty()) {
            // If no participants, use creator only
            $participants = collect([$travelRequest->user]);
        }
        // Remove the automatic creator addition logic

        foreach ($participants as $index => $peserta) {
            try {
                $variables = $this->getTemplateVariables($travelRequest, $peserta, $index + 1);
                
                $templateProcessor = new TemplateProcessor($templatePath);
                foreach ($variables as $key => $value) {
                    $templateProcessor->setValue($key, $value);
                }
                
                $fileName = $this->sanitizeFileName('SPPD-' . ($peserta->nip ?? $peserta->id) . '-' . now()->format('Y-m-d-H-i-s'));
                $tempPath = $this->normalizePath(storage_path('app/temp/' . $fileName . '.docx'));
                
                // Ensure directory exists
                $tempDir = dirname($tempPath);
                if (!file_exists($tempDir)) {
                    mkdir($tempDir, 0755, true);
                }
                
                $templateProcessor->saveAs($tempPath);
                
                if (file_exists($tempPath) && filesize($tempPath) > 0) {
                    chmod($tempPath, 0644); // Set proper permissions
                    $files[] = [
                        'name' => $fileName . '.docx',
                        'path' => $tempPath
                    ];
                    \Log::info('Generated DOCX for participant: ' . $peserta->name . ' (' . filesize($tempPath) . ' bytes)');
                } else {
                    \Log::error('Failed to create valid DOCX for participant: ' . $peserta->name);
                }
            } catch (\Exception $e) {
                \Log::error('Error processing template for participant ' . $peserta->id . ': ' . $e->getMessage());
                continue;
            }
        }
        
        return $files;
    }

    private function getTemplateVariables($travelRequest, $peserta, $lembarKe = 1)
    {
        $ppk = \App\Models\User::where('role', 'ppk')->first();
        $sekretaris = \App\Models\User::where('role', 'sekretaris')->first();
        
        return [
            'lembar_ke' => $lembarKe,
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
            'tempat_tujuan' => $travelRequest->tujuan,
            'durasi' => ($travelRequest->tanggal_berangkat && $travelRequest->tanggal_kembali)
                ? \Carbon\Carbon::parse($travelRequest->tanggal_berangkat)->diffInDays(\Carbon\Carbon::parse($travelRequest->tanggal_kembali)) + 1
                : '-',
            'tanggal_berangkat' => $travelRequest->tanggal_berangkat
                ? \Carbon\Carbon::parse($travelRequest->tanggal_berangkat)->locale('id')->translatedFormat('d F Y')
                : '-',
            'tanggal_kembali' => $travelRequest->tanggal_kembali
                ? \Carbon\Carbon::parse($travelRequest->tanggal_kembali)->locale('id')->translatedFormat('d F Y')
                : '-',
            'keterangan_pengajuan' => $travelRequest->catatan_pemohon ?? '-',
            'tanggal_surat_tugas' => $travelRequest->tanggal_surat_tugas
                ? \Carbon\Carbon::parse($travelRequest->tanggal_surat_tugas)->locale('id')->translatedFormat('d F Y')
                : '-',
            'nama_sekretaris' => $sekretaris ? $sekretaris->name : '-',
            'nip_sekretaris' => $sekretaris ? $sekretaris->nip : '-',
        ];
    }

    private function normalizePath($path)
    {
        // Convert backslashes to forward slashes for consistency
        $normalized = str_replace('\\', '/', $path);
        
        // Remove any double slashes (except for network paths)
        $normalized = preg_replace('#/+#', '/', $normalized);
        
        // Ensure proper directory separator for the current OS
        if (DIRECTORY_SEPARATOR === '\\') {
            // Windows: convert back to backslashes for file operations
            $normalized = str_replace('/', '\\', $normalized);
        }
        
        return $normalized;
    }

    private function sanitizeFileName($fileName)
    {
        // Remove illegal characters
        $illegal = ['/', '\\', ':', '*', '?', '"', '<', '>', '|', "\0"];
        $fileName = str_replace($illegal, '_', $fileName);
        
        // Remove extra spaces
        $fileName = preg_replace('/\s+/', ' ', trim($fileName));
        
        // Limit length
        if (strlen($fileName) > 100) {
            $fileName = substr($fileName, 0, 100);
        }
        
        return $fileName;
    }
} 