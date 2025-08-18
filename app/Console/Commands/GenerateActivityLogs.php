<?php

namespace App\Console\Commands;

use App\Models\TravelRequest;
use App\Services\ActivityLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate activity logs from existing travel requests';

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
        $this->info('Generating activity logs from existing travel requests...');

        try {
            $travelRequests = TravelRequest::with(['user', 'approvals.approver'])
                ->whereNotNull('submitted_at')
                ->get();

            $count = 0;

            foreach ($travelRequests as $travelRequest) {
                $userName = $travelRequest->user ? $travelRequest->user->name : 'Sistem';

                // Determine action and description based on status
                $action = 'SPPD Diperbarui';
                $description = "SPPD {$travelRequest->kode_sppd} telah diperbarui.";

                                switch ($travelRequest->status) {
                    case 'submitted':
                        $action = 'SPPD Dibuat';
                        $description = "Pengajuan SPPD atas nama {$userName} telah berhasil diajukan.";
                        break;
                    case 'in_review':
                        $approverRole = $travelRequest->current_approver_role ?? 'pihak berwenang';
                        $action = 'SPPD Dalam Review';

                        // Cek apakah ini approval oleh sekretaris atau status umum
                        $lastApproval = $travelRequest->approvals()->latest()->first();
                        $lastApproverRole = $lastApproval && $lastApproval->approver ? $lastApproval->approver->role : null;

                        if ($lastApproverRole === 'sekretaris' && $travelRequest->current_approval_level == 2) {
                            $description = "SPPD dengan nomor {$travelRequest->kode_sppd} telah disetujui Sekretaris dan menunggu persetujuan Pejabat Pembuat Komitmen.";
                        } else {
                            $description = "SPPD dengan nomor {$travelRequest->kode_sppd} sedang dalam tahap peninjauan dan evaluasi oleh {$approverRole}.";
                        }
                        break;
                    case 'revision':
                        $action = 'SPPD Perlu Revisi';
                        $lastApproval = $travelRequest->approvals()->latest()->first();
                        $approverName = $lastApproval && $lastApproval->approver ? $lastApproval->approver->name : 'approver';
                        $description = "SPPD dengan nomor {$travelRequest->kode_sppd} memerlukan perbaikan berdasarkan evaluasi dari {$approverName}.";
                        break;
                    case 'rejected':
                        $action = 'SPPD Ditolak';
                        $lastApproval = $travelRequest->approvals()->latest()->first();
                        $approverName = $lastApproval && $lastApproval->approver ? $lastApproval->approver->name : 'approver';
                        $applicantName = $travelRequest->user ? $travelRequest->user->name : 'Sistem';
                        $description = "SPPD yang diajukan oleh {$applicantName} tidak dapat melanjutkan proses dan telah ditolak oleh {$approverName}.";
                        break;
                    case 'completed':
                        $action = 'SPPD Disetujui';
                        $description = "Surat Perintah Perjalanan Dinas {$travelRequest->kode_sppd} telah disetujui lengkap.";
                        break;
                }

                // Create activity log
                $this->activityLogService->log(
                    $action,
                    TravelRequest::class,
                    $travelRequest->id,
                    [
                        'kode_sppd' => $travelRequest->kode_sppd,
                        'tujuan' => $travelRequest->tujuan,
                        'status' => $travelRequest->status,
                        'description' => $description,
                    ],
                    $travelRequest->user
                );

                $count++;
            }

            $this->info("Successfully generated {$count} activity logs.");
            return 0;
        } catch (\Exception $e) {
            $this->error('Error generating activity logs: ' . $e->getMessage());
            Log::error('Error generating activity logs: ' . $e->getMessage());
            return 1;
        }
    }
}
