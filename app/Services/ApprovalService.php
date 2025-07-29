<?php

namespace App\Services;

use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ApprovalService
 *
 * Handles all business logic related to SPPD approval workflow
 * Manages complex approval flow based on user roles and hierarchy
 */
class ApprovalService
{
    /**
     * Determine approval flow based on submitter role
     *
     * @param User $submitter
     * @return array
     */
    public function getApprovalFlow(): array
    {
        // Hanya kasubbag yang bisa submit
        return [
            'kasubbag' => ['sekretaris', 'ppk'],
        ];
    }

    /**
     * Get current approval level for a role in the approval flow
     *
     * @param string $role
     * @param TravelRequest $travelRequest
     * @return int
     */
    public function getApprovalLevel(string $role, TravelRequest $travelRequest): int
    {
        $submitterRole = $travelRequest->user->role;
        $flow = $this->getApprovalFlowForSubmitter($submitterRole);
        foreach ($flow as $level => $flowRole) {
            if ($flowRole === $role) {
                return $level;
            }
        }
        return 1;
    }

    /**
     * Get next approver role in the workflow
     *
     * @param TravelRequest $travelRequest
     * @param string $currentRole
     * @return string|null
     */
    public function getNextApproverRole(TravelRequest $travelRequest, string $currentRole): ?string
    {
        $flow = $this->getApprovalFlow();
        $currentIndex = array_search($currentRole, $flow);

        if ($currentIndex === false || $currentIndex >= count($flow) - 1) {
            return null; // No next approver or final approval
        }

        return $flow[$currentIndex + 1];
    }

    /**
     * Mapping approval flow: HANYA kasubbag yang bisa mengajukan SPPD
     * Alur: kasubbag submit -> 1. Sekretaris, 2. PPK
     */
    public function getApprovalFlowForSubmitter($submitterRole)
    {
        if ($submitterRole === 'kasubbag') {
            return [
                1 => 'sekretaris',
                2 => 'ppk',
            ];
        }
        // Role lain tidak bisa submit SPPD
        return [];
    }

    /**
     * Cek apakah user bisa approve SPPD ini
     */
    public function canUserApprove(TravelRequest $travelRequest, User $user, $forReject = false): bool
    {
        // Tidak bisa approve/reject jika sudah selesai atau ditolak
        if (!in_array($travelRequest->status, ['in_review'])) {
            return false;
        }
        // Tambahan: admin tidak boleh approve/reject SPPD
        if ($user->role === 'admin') {
            return false;
        }
        $submitterRole = $travelRequest->user->role;
        $flow = $this->getApprovalFlowForSubmitter($submitterRole);
        $currentLevel = $travelRequest->current_approval_level;
        // Cek apakah user adalah approver di level saat ini
        if (!isset($flow[$currentLevel]) || $flow[$currentLevel] !== $user->role) {
            return false;
        }
        // Untuk approve: tidak boleh sudah approve
        if (!$forReject) {
        $existing = Approval::where('travel_request_id', $travelRequest->id)
            ->where('approver_id', $user->id)
            ->where('status', 'approved')
            ->first();
        if ($existing) {
            return false;
            }
        } else {
            // Untuk reject: tidak boleh sudah reject
            $existing = Approval::where('travel_request_id', $travelRequest->id)
                ->where('approver_id', $user->id)
                ->where('status', 'rejected')
                ->first();
            if ($existing) {
                return false;
            }
        }
        return true;
    }

    /**
     * Process approval for travel request
     * 
     * WORKFLOW LOGIC:
     * 
     * KASUBBAG submits SPPD:
     * 1. Kasubbag submits SPPD → status: 'in_review', current_approval_level: 1
     * 2. Sekretaris approves → status: 'in_review', current_approval_level: 2
     * 3. PPK approves → status: 'completed' ✅
     * 
     * Status 'completed' is ONLY set when ALL required approvals are completed.
     *
     * @param TravelRequest $travelRequest
     * @param User $approver
     * @param string $comments
     * @return bool
     * @throws \Exception
     */
    public function processTravelRequestApproval(TravelRequest $travelRequest, User $approver, string $comments = ''): bool
    {
        if (!$this->canUserApprove($travelRequest, $approver)) {
            throw new \Exception('User tidak dapat melakukan approval untuk SPPD ini.');
        }

        DB::beginTransaction();
        try {
            // Cek apakah approval sudah ada untuk level ini
            $approval = Approval::where('travel_request_id', $travelRequest->id)
                ->where('level', $this->getApprovalLevel($approver->role, $travelRequest))
                ->first();
            
            if ($approval) {
                // Update approval yang sudah ada
                $approval->update([
                    'approver_id' => $approver->id,
                    'role' => $approver->role,
                    'status' => 'approved',
                    'comments' => $comments,
                    'approved_at' => now(),
                ]);
            } else {
                // Buat approval baru dengan semua field yang diperlukan
                $approval = Approval::create([
                    'travel_request_id' => $travelRequest->id,
                    'approver_id' => $approver->id,
                    'level' => $this->getApprovalLevel($approver->role, $travelRequest),
                    'role' => $approver->role,
                    'status' => 'approved',
                    'comments' => $comments,
                    'approved_at' => now(),
                ]);
            }

            // Update approval history
            $this->updateApprovalHistory($travelRequest, $approver, 'completed', $comments);

            // Determine next step
            $submitterRole = $travelRequest->user->role;
            $flow = $this->getApprovalFlowForSubmitter($submitterRole);
            $currentLevel = $travelRequest->current_approval_level;
            $nextLevel = $currentLevel + 1;
            $maxLevel = count($flow);
            
            // Check if all required approvals are completed
            $allApprovalsCompleted = true;
            for ($level = 1; $level <= $maxLevel; $level++) {
                $approval = Approval::where('travel_request_id', $travelRequest->id)
                    ->where('level', $level)
                    ->where('status', 'approved')
                    ->first();
                if (!$approval) {
                    $allApprovalsCompleted = false;
                    break;
                }
            }
            
            if ($allApprovalsCompleted) {
                // All approvals completed, set status to completed
                // Generate kode SPD jika belum ada
                if (empty($travelRequest->kode_sppd)) {
                    $kodeSppd = app(\App\Services\TravelRequestService::class)->generateKodeSppd();
                    $travelRequest->kode_sppd = $kodeSppd;
                }
                // Generate nomor surat tugas dan tanggal surat tugas saat approve
                if (empty($travelRequest->nomor_surat_tugas)) {
                    $nomorSuratTugas = app(\App\Services\TravelRequestService::class)->generateNomorSuratTugas();
                    $travelRequest->nomor_surat_tugas = $nomorSuratTugas;
                }
                $travelRequest->update([
                    'status' => 'completed',
                    'current_approval_level' => 0,
                    'approved_at' => now(),
                    'updated_at' => now(),
                    'kode_sppd' => $travelRequest->kode_sppd,
                    'nomor_surat_tugas' => $travelRequest->nomor_surat_tugas,
                    'tanggal_surat_tugas' => now()->format('Y-m-d'),
                ]);
            } else {
                // Still need more approvals, move to next level
                $travelRequest->update([
                    'current_approval_level' => $nextLevel,
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval process failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process rejection for travel request
     *
     * @param TravelRequest $travelRequest
     * @param User $approver
     * @param string $reason
     * @return bool
     * @throws \Exception
     */
    public function processTravelRequestRejection(TravelRequest $travelRequest, User $approver, string $reason): bool
    {
        if (!$this->canUserApprove($travelRequest, $approver, true)) {
            throw new \Exception('User tidak dapat melakukan rejection untuk SPPD ini.');
        }

        DB::beginTransaction();
        try {
            // Cek apakah approval sudah ada untuk level ini
            $approval = Approval::where('travel_request_id', $travelRequest->id)
                ->where('level', $this->getApprovalLevel($approver->role, $travelRequest))
                ->first();
            
            if ($approval) {
                // Update approval yang sudah ada
                $approval->update([
                    'approver_id' => $approver->id,
                    'role' => $approver->role,
                    'status' => 'rejected',
                    'comments' => $reason,
                    'rejected_at' => now(),
                ]);
            } else {
                // Buat approval baru dengan semua field yang diperlukan
                $approval = Approval::create([
                    'travel_request_id' => $travelRequest->id,
                    'approver_id' => $approver->id,
                    'level' => $this->getApprovalLevel($approver->role, $travelRequest),
                    'role' => $approver->role,
                    'status' => 'rejected',
                    'comments' => $reason,
                    'rejected_at' => now(),
                ]);
            }

            // Update approval history
            $this->updateApprovalHistory($travelRequest, $approver, 'rejected', $reason);

            // Mark travel request as rejected
            $travelRequest->update([
                'status' => 'rejected',
                'current_approval_level' => 0,
                'catatan_approval' => $reason,
                'updated_at' => now(),
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rejection process failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process revision request for travel request
     *
     * @param TravelRequest $travelRequest
     * @param User $approver
     * @param string $reason
     * @param string $target
     * @return bool
     * @throws \Exception
     */
    public function processTravelRequestRevision(TravelRequest $travelRequest, User $approver, string $reason, string $target): bool
    {
        if (!$this->canUserApprove($travelRequest, $approver)) {
            throw new \Exception('User tidak dapat melakukan revision untuk SPPD ini.');
        }

        DB::beginTransaction();
        try {
            // Cek apakah approval sudah ada untuk level ini
            $approval = Approval::where('travel_request_id', $travelRequest->id)
                ->where('level', $this->getApprovalLevel($approver->role, $travelRequest))
                ->first();
            
            if ($approval) {
                // Update approval yang sudah ada
                $approval->update([
                    'approver_id' => $approver->id,
                    'role' => $approver->role,
                    'status' => 'revision_minor',
                    'comments' => $reason,
                    'rejected_at' => now(),
                ]);
            } else {
                // Buat approval baru dengan semua field yang diperlukan
                $approval = Approval::create([
                    'travel_request_id' => $travelRequest->id,
                    'approver_id' => $approver->id,
                    'level' => $this->getApprovalLevel($approver->role, $travelRequest),
                    'role' => $approver->role,
                    'status' => 'revision_minor',
                    'comments' => $reason,
                    'rejected_at' => now(),
                ]);
            }

            // Update approval history with target information
            $this->updateApprovalHistory($travelRequest, $approver, 'revision', $reason, $target);

            // Tentukan target level berdasarkan target revisi
            if ($target === 'kasubbag') {
                // Revisi ke kasubbag - status revision, level 0
                $targetLevel = 0;
                $newStatus = 'revision';
            } else {
                // Revisi ke sekretaris - status in_review, level 1
                $targetLevel = 1;
                $newStatus = 'in_review';
            }

            $travelRequest->update([
                'status' => $newStatus,
                'current_approval_level' => $targetLevel,
                'catatan_approval' => $reason,
                'updated_at' => now(),
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Revision process failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update approval history in travel request
     *
     * @param TravelRequest $travelRequest
     * @param User $approver
     * @param string $status
     * @param string $reason
     * @param string|null $target
     * @return void
     */
    protected function updateApprovalHistory(TravelRequest $travelRequest, User $approver, string $status, string $reason = '', ?string $target = null): void
    {
        // Ambil seluruh approval dari tabel approvals
        $approvals = $travelRequest->approvals()->get();
        $history = [];
        foreach ($approvals as $approval) {
            $approver = $approval->approver;
            $historyEntry = [
                'role' => $approval->role,
                'approved_by' => $approver ? $approver->name : null,
                'approver_avatar' => $approver ? $approver->avatar_url : null,
                'approver_id' => $approver ? $approver->id : null,
                'status' => $approval->status,
                'timestamp' => $approval->approved_at ? $approval->approved_at->toDateTimeString() : null,
                'reason' => $approval->comments ?? '',
            ];
            $history[] = $historyEntry;
        }
        $travelRequest->update([
            'approval_history' => json_encode($history)
        ]);
    }

    /**
     * Get pending approvals for a specific role
     *
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingApprovalsForRole(string $role)
    {
        $requests = TravelRequest::with('user')
            ->where('status', 'in_review')
            ->where('current_approval_level', '>', 0)
            ->orderBy('submitted_at', 'asc')
            ->get();

        // Filter by current approver role using the accessor
        return $requests->filter(function($request) use ($role) {
            return $request->current_approver_role === $role;
        });
    }

    /**
     * Initialize approval workflow when SPPD is submitted
     *
     * @param TravelRequest $travelRequest
     * @return void
     */
    public function initializeApprovalWorkflow(TravelRequest $travelRequest): void
    {
        $submitterRole = $travelRequest->user->role;
        $flow = $this->getApprovalFlowForSubmitter($submitterRole);
        
        \Log::info('Initializing approval workflow:', [
            'sppd_id' => $travelRequest->id,
            'submitter_role' => $submitterRole,
            'approval_flow' => $flow,
            'before_status' => $travelRequest->status,
            'before_approval_level' => $travelRequest->current_approval_level
        ]);

        if (empty($flow)) {
            // No approval needed, mark as completed immediately
            $travelRequest->update([
                'status' => 'completed',
                'approved_at' => now(),
            ]);
            
            \Log::info('No approval flow found, marking as completed:', [
                'sppd_id' => $travelRequest->id,
                'after_status' => 'completed'
            ]);
            return;
        }

        // Set to first approver in the flow
        $travelRequest->update([
            'status' => 'in_review',
            'current_approval_level' => 1,
        ]);
        
        \Log::info('Approval workflow initialized:', [
            'sppd_id' => $travelRequest->id,
            'after_status' => 'in_review',
            'after_approval_level' => 1,
            'first_approver_role' => $flow[1] ?? null
        ]);
    }

    /**
     * Initialize approval workflow after revision
     * This method determines the correct approval level based on previous approvals
     */
    public function initializeApprovalWorkflowAfterRevision(TravelRequest $travelRequest): void
    {
        $submitterRole = $travelRequest->user->role;
        $flow = $this->getApprovalFlowForSubmitter($submitterRole);
        
        \Log::info('Initializing approval workflow after revision:', [
            'sppd_id' => $travelRequest->id,
            'submitter_role' => $submitterRole,
            'approval_flow' => $flow,
            'before_status' => $travelRequest->status,
            'before_approval_level' => $travelRequest->current_approval_level
        ]);

        if (empty($flow)) {
            // No approval needed, mark as completed immediately
            $travelRequest->update([
                'status' => 'completed',
                'approved_at' => now(),
            ]);
            
            \Log::info('No approval flow found, marking as completed:', [
                'sppd_id' => $travelRequest->id,
                'after_status' => 'completed'
            ]);
            return;
        }

        // Check current approval level and determine where to send
        $currentLevel = $travelRequest->current_approval_level;
        
        // Get the flow for this submitter
        $flow = $this->getApprovalFlowForSubmitter($submitterRole);
        
        // Determine the next approval level based on current level
        $nextLevel = $this->determineNextApprovalLevelAfterRevision($travelRequest, $currentLevel, $flow);
        
        \Log::info('Revision workflow analysis:', [
            'sppd_id' => $travelRequest->id,
            'current_level' => $currentLevel,
            'next_level' => $nextLevel,
            'flow' => $flow
        ]);

        // If all approvals are done, mark as completed
        if ($travelRequest->status === 'completed') {
            \Log::info('SPPD already marked as completed:', [
                'sppd_id' => $travelRequest->id,
                'status' => $travelRequest->status
            ]);
            return;
        }

        $travelRequest->update([
            'status' => 'in_review',
            'current_approval_level' => $nextLevel,
        ]);
        
        \Log::info('Approval workflow initialized after revision:', [
            'sppd_id' => $travelRequest->id,
            'after_status' => 'in_review',
            'after_approval_level' => $nextLevel,
            'next_approver_role' => $flow[$nextLevel] ?? null
        ]);
    }

    /**
     * Determine the next approval level based on previous approvals
     */
    private function determineNextApprovalLevel(TravelRequest $travelRequest, array $approvedLevels, array $flow): int
    {
        // If no previous approvals, start from level 1
        if (empty($approvedLevels)) {
            return 1;
        }

        // Find the highest approved level
        $highestApprovedLevel = max($approvedLevels);
        
        // Get the next level in the flow
        $nextLevel = $highestApprovedLevel + 1;
        
        // Check if this level exists in the flow
        if (isset($flow[$nextLevel])) {
            return $nextLevel;
        }
        
        // If next level doesn't exist, it means all approvals are done
        // Mark as completed
        $travelRequest->update([
            'status' => 'completed',
            'approved_at' => now(),
        ]);
        
        \Log::info('All approvals completed, marking as completed:', [
            'sppd_id' => $travelRequest->id,
            'highest_approved_level' => $highestApprovedLevel,
            'flow' => $flow
        ]);
        
        return 0; // Return 0 to indicate completed
    }

    /**
     * Determine the next approval level after revision
     * This method looks at the current level where revision happened
     */
    private function determineNextApprovalLevelAfterRevision(TravelRequest $travelRequest, int $currentLevel, array $flow): int
    {
        // If current level is 0 (just created), start from level 1
        if ($currentLevel === 0) {
            return 1;
        }

        // If current level exists in flow, return that level
        // This means we go back to the same level where revision happened
        if (isset($flow[$currentLevel])) {
            return $currentLevel;
        }

        // If current level doesn't exist in flow, it means all approvals are done
        // Mark as completed
        $travelRequest->update([
            'status' => 'completed',
            'approved_at' => now(),
        ]);
        
        \Log::info('All approvals completed after revision, marking as completed:', [
            'sppd_id' => $travelRequest->id,
            'current_level' => $currentLevel,
            'flow' => $flow
        ]);
        
        return 0; // Return 0 to indicate completed
    }

    /**
     * Get approval progress for display
     *
     * @param TravelRequest $travelRequest
     * @return array
     */
    public function getApprovalProgress(TravelRequest $travelRequest): array
    {
        $submitterRole = $travelRequest->user->role;
        $flow = $this->getApprovalFlowForSubmitter($submitterRole);
        $approvals = $travelRequest->approvals()->get()->keyBy('role');

        $progress = [];
        foreach ($flow as $level => $role) {
            $approval = $approvals->get($role);
            $progress[] = [
                'role' => $role,
                'level' => $level,
                'status' => $approval ? $approval->status : 'pending',
                'approver' => $approval ? $approval->approver->name : null,
                'approved_at' => $approval ? $approval->approved_at : null,
                'is_current' => $travelRequest->current_approver_role === $role,
            ];
        }

        return $progress;
    }

    /**
     * Check if travel request needs approval
     *
     * @param TravelRequest $travelRequest
     * @return bool
     */
    public function needsApproval(TravelRequest $travelRequest): bool
    {
        $submitterRole = $travelRequest->user->role;
        $flow = $this->getApprovalFlowForSubmitter($submitterRole);
        return !empty($flow);
    }

    /**
     * Check if user can view a travel request
     *
     * @param TravelRequest $travelRequest
     * @param User $user
     * @return bool
     */
    public function canUserViewTravelRequest(TravelRequest $travelRequest, User $user): bool
    {
        // SPPD dalam review dan menunggu approval dari role ini
        if ($travelRequest->status === 'in_review' && $travelRequest->current_approver_role === $user->role) {
            return true;
        }

        // Pimpinan bisa melihat SPPD yang sudah completed/rejected (untuk history)
        if (in_array($travelRequest->status, ['completed', 'rejected']) &&
            in_array($user->role, ['sekretaris', 'ppk'])) {
            return true;
        }

        // Pimpinan bisa melihat SPPD yang diajukan oleh level di bawahnya berdasarkan hierarchy
        $submitterRole = $travelRequest->user->role;
        $flow = $this->getApprovalFlowForSubmitter($submitterRole);
        return in_array($user->role, $flow);
    }

    /**
     * Get approval statistics for a role
     *
     * @param string $role
     * @return array
     */
    public function getApprovalStatistics(string $role): array
    {
        $pendingRequests = $this->getPendingApprovalsForRole($role);

        return [
            'total_pending' => $pendingRequests->count(),
            'urgent' => $pendingRequests->where('is_urgent', true)->count(),
            'from_sekretaris' => $pendingRequests->filter(function($req) {
                return $req->user->role === 'sekretaris';
            })->count(),
            'from_kasubbag' => $pendingRequests->filter(function($req) {
                return $req->user->role === 'kasubbag';
            })->count(),
            'from_ppk' => $pendingRequests->filter(function($req) {
                return $req->user->role === 'ppk';
            })->count(),
            'from_staff' => $pendingRequests->filter(function($req) {
                return !in_array($req->user->role, ['sekretaris', 'kasubbag', 'ppk']);
            })->count(),
        ];
    }

    /**
     * Get approval dashboard statistics
     *
     * @param string $role
     * @return array
     */
    public function getApprovalDashboardStats(string $role): array
    {
        // Get all pending requests and filter by current approver role
        $pendingRequests = TravelRequest::where('status', 'in_review')
            ->where('current_approval_level', '>', 0)
            ->with('user')
            ->get()
            ->filter(function($request) use ($role) {
                return $request->current_approver_role === $role;
            });

        return [
            'pending' => $pendingRequests->count(),
            'approved_today' => TravelRequest::whereHas('approvals', function($query) use ($role) {
                $query->where('role', $role)
                      ->where('status', 'approved')
                      ->whereDate('approved_at', today());
            })->count(),
            'rejected_today' => TravelRequest::whereHas('approvals', function($query) use ($role) {
                $query->where('role', $role)
                      ->where('status', 'rejected')
                      ->whereDate('rejected_at', today());
            })->count(),
            'total_processed' => TravelRequest::whereHas('approvals', function($query) use ($role) {
                $query->where('role', $role)
                      ->whereIn('status', ['completed', 'rejected']);
            })->count(),
        ];
    }
}
