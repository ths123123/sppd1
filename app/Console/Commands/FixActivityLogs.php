<?php

namespace App\Console\Commands;

use App\Models\TravelRequest;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:fix {--clean : Clean up invalid activity logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix and clean up activity logs with proper messages';

    /**
     * @var ActivityLogService
     */
    protected $activityLogService;

    /**
     * Create a new command instance.
     *
     * @param ActivityLogService $activityLogService
     * @return void
     */
    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->activityLogService = $activityLogService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fixing and cleaning up activity logs...');

        try {
            // Clean up invalid activity logs if requested
            if ($this->option('clean')) {
                $this->cleanInvalidLogs();
            }

            // Fix existing activity logs
            $this->fixExistingLogs();

            // Generate missing activity logs
            $this->generateMissingLogs();

            $this->info('Activity logs have been fixed successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error fixing activity logs: ' . $e->getMessage());
            Log::error('Error fixing activity logs: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Clean up invalid activity logs
     */
    private function cleanInvalidLogs()
    {
        $this->info('Cleaning up invalid activity logs...');

        // Remove activity logs with empty descriptions or invalid data
        $invalidLogs = ActivityLog::where(function($query) {
            $query->whereNull('details')
                  ->orWhereRaw("details::text = '{}'")
                  ->orWhereRaw("details::text = '[]'");
        })->orWhere('action', '')->orWhere('action', null)->delete();

        $this->info("Removed {$invalidLogs} invalid activity logs.");
    }

    /**
     * Fix existing activity logs
     */
    private function fixExistingLogs()
    {
        $this->info('Fixing existing activity logs...');

        $activityLogs = ActivityLog::where('model_type', TravelRequest::class)->get();
        $fixedCount = 0;

        foreach ($activityLogs as $log) {
            $travelRequest = TravelRequest::find($log->model_id);

            if (!$travelRequest) {
                // Remove orphaned activity logs
                $log->delete();
                continue;
            }

            $details = $log->details ?? [];
            $needsUpdate = false;

            // Fix missing kode_sppd
            if (empty($details['kode_sppd']) && $travelRequest->kode_sppd) {
                $details['kode_sppd'] = $travelRequest->kode_sppd;
                $needsUpdate = true;
            }

            // Fix missing tujuan
            if (empty($details['tujuan']) && $travelRequest->tujuan) {
                $details['tujuan'] = $travelRequest->tujuan;
                $needsUpdate = true;
            }

            // Fix description based on current status
            $newDescription = $this->generateProperDescription($travelRequest, $log->action);
            if (empty($details['description']) || $details['description'] !== $newDescription) {
                $details['description'] = $newDescription;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $log->update(['details' => $details]);
                $fixedCount++;
            }
        }

        $this->info("Fixed {$fixedCount} activity logs.");
    }

    /**
     * Generate missing activity logs
     */
    private function generateMissingLogs()
    {
        $this->info('Generating missing activity logs...');

        $travelRequests = TravelRequest::with(['user', 'approvals.approver'])
            ->whereNotNull('submitted_at')
            ->get();

        $generatedCount = 0;

        foreach ($travelRequests as $travelRequest) {
            // Check if we already have activity logs for this travel request
            $existingLogs = ActivityLog::where('model_type', TravelRequest::class)
                ->where('model_id', $travelRequest->id)
                ->count();

            if ($existingLogs === 0) {
                // Generate activity log based on current status
                $this->generateActivityLogForTravelRequest($travelRequest);
                $generatedCount++;
            }
        }

        $this->info("Generated {$generatedCount} missing activity logs.");
    }

    /**
     * Generate proper description for travel request
     */
    private function generateProperDescription(TravelRequest $travelRequest, string $action): string
    {
        $userName = $travelRequest->user ? $travelRequest->user->name : 'Sistem';
        $kodeSppd = $travelRequest->kode_sppd ?: 'No. SPPD belum tersedia';

                switch ($travelRequest->status) {
            case 'submitted':
                return "Pengajuan SPPD atas nama {$userName} telah berhasil diajukann.";
            
            case 'in_review':
                // Check if this is approval by sekretaris
                $lastApproval = $travelRequest->approvals()->latest()->first();
                $lastApproverRole = $lastApproval && $lastApproval->approver ? $lastApproval->approver->role : null;
                
                if ($lastApproverRole === 'sekretaris' && $travelRequest->current_approval_level == 2) {
                    return "SPPD dengan nomor {$kodeSppd} telah disetujui Sekretaris dan menunggu persetujuan Pejabat Pembuat Komitmen.";
                } else {
                    $approverRole = $travelRequest->current_approver_role ?? 'pihak berwenang';
                    return "SPPD dengan nomor {$kodeSppd} sedang dalam tahap peninjauan dan evaluasi oleh {$approverRole}.";
                }
            
            case 'revision':
                $lastApproval = $travelRequest->approvals()->latest()->first();
                $approverName = $lastApproval && $lastApproval->approver ? $lastApproval->approver->name : 'approver';
                return "SPPD dengan nomor {$kodeSppd} memerlukan perbaikan berdasarkan evaluasi dari {$approverName}.";
            
            case 'rejected':
                $lastApproval = $travelRequest->approvals()->latest()->first();
                $approverName = $lastApproval && $lastApproval->approver ? $lastApproval->approver->name : 'approver';
                $applicantName = $travelRequest->user ? $travelRequest->user->name : 'Sistem';
                return "SPPD yang diajukan oleh {$applicantName} tidak dapat melanjutkan proses dan telah ditolak oleh {$approverName}.";
            
            case 'completed':
                return "Surat Perintah Perjalanan Dinas {$kodeSppd} telah disetujui lengkap.";
            
            default:
                return "SPPD {$kodeSppd} telah diperbarui.";
        }
    }

    /**
     * Generate activity log for travel request
     */
    private function generateActivityLogForTravelRequest(TravelRequest $travelRequest)
    {
        $userName = $travelRequest->user ? $travelRequest->user->name : 'Sistem';
        $kodeSppd = $travelRequest->kode_sppd ?: 'No. SPPD belum tersedia';

        // Determine action and description based on status
        $action = 'SPPD Diperbarui';
        $description = $this->generateProperDescription($travelRequest, $action);

        switch ($travelRequest->status) {
            case 'submitted':
                $action = 'SPPD Dibuat';
                break;
            case 'in_review':
                $action = 'SPPD Dalam Review';
                break;
            case 'revision':
                $action = 'SPPD Perlu Revisi';
                break;
            case 'rejected':
                $action = 'SPPD Ditolak';
                break;
            case 'completed':
                $action = 'SPPD Disetujui';
                break;
        }

        // Create activity log
        $this->activityLogService->log(
            $action,
            TravelRequest::class,
            $travelRequest->id,
            [
                'kode_sppd' => $kodeSppd,
                'tujuan' => $travelRequest->tujuan,
                'status' => $travelRequest->status,
                'description' => $description,
                'user_name' => $userName,
                'user_role' => $travelRequest->user ? $travelRequest->user->role : 'system',
            ],
            $travelRequest->user
        );
    }
}
