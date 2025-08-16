<?php

namespace App\Observers;

use App\Models\TravelRequest;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class TravelRequestObserver
{
    /**
     * @var ActivityLogService
     */
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Handle the TravelRequest "created" event.
     *
     * @param  \App\Models\TravelRequest  $travelRequest
     * @return void
     */
    public function created(TravelRequest $travelRequest)
    {
        // Log SPPD creation
        $user = $travelRequest->user ?? Auth::user();
        $userName = $user ? $user->name : 'Sistem';

        $action = 'SPPD Dibuat';
        $description = "📋 SPPD baru dengan tujuan {$travelRequest->tujuan} telah dibuat oleh {$userName}.";

        $this->activityLogService->log(
            $action,
            TravelRequest::class,
            $travelRequest->id,
            [
                'kode_sppd' => $travelRequest->kode_sppd,
                'tujuan' => $travelRequest->tujuan,
                'status' => $travelRequest->status,
                'description' => $description,
                'user_name' => $userName,
                'user_role' => $user ? $user->role : 'system',
            ]
        );
    }

    /**
     * Handle the TravelRequest "updated" event.
     *
     * @param  \App\Models\TravelRequest  $travelRequest
     * @return void
     */
    public function updated(TravelRequest $travelRequest)
    {
        // Log when SPPD is submitted (submitted_at changes from null to timestamp)
        if ($travelRequest->wasChanged('submitted_at') && $travelRequest->submitted_at !== null) {
            $user = $travelRequest->user ?? Auth::user();
            $userName = $user ? $user->name : 'Sistem';

            $action = 'SPPD Diajukan';
            $description = "📋 SPPD dengan tujuan {$travelRequest->tujuan} telah berhasil diajukan oleh {$userName} untuk proses persetujuan.";

            $this->activityLogService->log(
                $action,
                TravelRequest::class,
                $travelRequest->id,
                [
                    'kode_sppd' => $travelRequest->kode_sppd,
                    'tujuan' => $travelRequest->tujuan,
                    'status' => $travelRequest->status,
                    'description' => $description,
                    'user_name' => $userName,
                    'user_role' => $user ? $user->role : 'system',
                    'submitted_at' => $travelRequest->submitted_at,
                ]
            );
        }

        // Log when status changes
        if ($travelRequest->wasChanged('status')) {
            $oldStatus = $travelRequest->getOriginal('status');
            $newStatus = $travelRequest->status;

            // Use the user from the request, fallback to the authenticated user
            $user = $travelRequest->user ?? Auth::user();
            $userName = $user ? $user->name : 'Sistem';

            $description = "Status SPPD {$travelRequest->kode_sppd} diubah dari '{$oldStatus}' menjadi '{$newStatus}'.";
            $action = "SPPD Diperbarui";

            // Dapatkan informasi approver terakhir jika ada
            $lastApprover = $travelRequest->approvals()->latest()->first();
            $approverName = $lastApprover && $lastApprover->user ? $lastApprover->user->name : null;
            $approverRole = $lastApprover && $lastApprover->user ? $lastApprover->user->role : null;

            switch ($newStatus) {
                case 'in_review':
                    $currentApproverRole = $travelRequest->current_approver_role ?? 'pihak berwenang';
                    $action = 'SPPD Dalam Review';
                    $description = "⏳ SPPD {$travelRequest->kode_sppd} sedang dalam tahap peninjauan dan evaluasi oleh {$currentApproverRole}.";
                    break;
                case 'revision':
                    $action = 'SPPD Perlu Revisi';
                    $description = "🔄 SPPD {$travelRequest->kode_sppd} memerlukan perbaikan berdasarkan evaluasi dari {$approverName}.";
                    break;
                case 'rejected':
                    $action = 'SPPD Ditolak';
                    $description = "❌ SPPD {$travelRequest->kode_sppd} tidak dapat diproses dan telah ditolak oleh {$approverName}.";
                    break;
                case 'completed':
                    $action = 'SPPD Disetujui';
                    $description = "✅ SPPD {$travelRequest->kode_sppd} telah memperoleh persetujuan penuh dan siap untuk eksekusi perjalanan dinas";
                    if ($approverName) {
                        $description .= " berdasarkan evaluasi dari {$approverName}";
                    }
                    $description .= ".";
                    break;
            }

            $this->activityLogService->log(
                $action,
                TravelRequest::class,
                $travelRequest->id,
                [
                    'kode_sppd' => $travelRequest->kode_sppd,
                    'tujuan' => $travelRequest->tujuan,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'description' => $description,
                    'approver_name' => $approverName,
                    'approver_role' => $approverRole,
                ]
            );
        }
    }

    /**
     * Handle the TravelRequest "deleted" event.
     *
     * @param  \App\Models\TravelRequest  $travelRequest
     * @return void
     */
    public function deleted(TravelRequest $travelRequest)
    {
        $this->activityLogService->log(
            'SPPD Dihapus',
            TravelRequest::class,
            $travelRequest->id,
            [
                'kode_sppd' => $travelRequest->kode_sppd,
                'tujuan' => $travelRequest->tujuan,
                'description' => "SPPD {$travelRequest->kode_sppd} dengan tujuan {$travelRequest->tujuan} telah dihapus."
            ]
        );
    }
}
