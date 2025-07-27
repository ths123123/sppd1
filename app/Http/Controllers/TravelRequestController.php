<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use App\Services\ApprovalService;
use App\Services\CalculationService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Collection;

use App\Http\Requests\TravelRequestUpdateRequest;
use App\Http\Requests\TravelRequestStoreRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\SppdResubmitted;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\BudgetCalculationTrait;
use App\Services\DocumentService;
use App\Enums\TravelRequestStatus;

class TravelRequestController extends Controller
{
    use AuthorizesRequests, BudgetCalculationTrait;
    
    // Constants
    private const ALLOWED_ROLES_FOR_ACCESS = ['kasubbag', 'sekretaris', 'ppk', 'admin'];
    private const MANAGEMENT_ROLES = ['kasubbag', 'sekretaris', 'ppk'];
    private const ITEMS_PER_PAGE = 10;
    private const PENDING_APPROVALS_LIMIT = 5;
    
    protected ApprovalService $approvalService;
    protected CalculationService $calculationService;
    protected NotificationService $notificationService;
    protected DocumentService $documentService;

    public function __construct(
        TravelRequestService $travelRequestService,
        ApprovalService $approvalService,
        CalculationService $calculationService,
        NotificationService $notificationService,
        DocumentService $documentService
    ) {
        $this->travelRequestService = $travelRequestService;
        $this->approvalService = $approvalService;
        $this->calculationService = $calculationService;
        $this->notificationService = $notificationService;
        $this->documentService = $documentService;
    }

    /**
     * Unified index method - handles both 'my' and 'all' scopes
     */
    public function index(Request $request)
    {
        $scope = $this->getUserScope();
        
        return match($scope) {
            'all' => $this->indexAll($request),
            'my' => $this->getMyTravelRequests($request),
            default => $this->getMyTravelRequests($request)
        };
    }

    /**
     * Daftar SEMUA SPPD (untuk kasubbag, sekretaris, ppk)
     */
    public function indexAll(Request $request)
    {
        $this->authorize('viewAny', TravelRequest::class);
        $currentUser = Auth::user();

        // Validasi input request dengan enum
        $validated = $request->validate([
            'search' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'status' => 'nullable|string|in:' . implode(',', TravelRequestStatus::toArray()),
            'year' => 'nullable|integer|min:2025|max:2030',
            'page' => 'nullable|integer|min:1|max:1000',
        ]);

        // Query awal dengan relasi yang dibutuhkan
        $query = TravelRequest::with('user:id,name,role')->orderByDesc('created_at');

        // Terapkan filter dari service
        $travelRequests = $this->travelRequestService->filterTravelRequests($validated, $query)->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('travel_requests.partials.sppd_table', compact('travelRequests'))->render();
        }

        return view('travel_requests.daftar-sppd', compact('travelRequests', 'currentUser'));
    }



    /**
     * Get user's travel requests with optimized query
     */
    private function getMyTravelRequests(Request $request)
    {
        try {
            $user = Auth::user();
            $filters = $request->only(['status', 'search', 'date_from', 'date_to']);

            // Optimized query with eager loading
            $travelRequests = TravelRequest::with([
                'user:id,name,role',
                'participants:id,name',
                'approvals' => fn($q) => $q->latest()->limit(1)->with('approver:id,name')
            ])
            ->where('user_id', $user->id)
            ->orWhereHas('participants', fn($q) => $q->where('users.id', $user->id))
            ->select('travel_requests.*')
            ->groupBy('travel_requests.id')
            ->orderByDesc('created_at')
            ->paginate(self::ITEMS_PER_PAGE);

            $stats = $this->travelRequestService->getUserStatistics($user->id);
            // Normalisasi key agar tidak error di Blade
            $stats = array_merge([
                'total' => 0,
                'menunggu' => 0,
                'disetujui' => 0,
                'draft' => 0,
            ], $stats);
            
            $pendingApprovals = TravelRequest::where('user_id', $user->id)
                ->where('status', TravelRequestStatus::IN_REVIEW->value)
                ->orderByDesc('created_at')
                ->limit(self::PENDING_APPROVALS_LIMIT)
                ->get();

            return view('travel_requests.my-requests', compact('travelRequests', 'filters', 'stats', 'pendingApprovals'));

        } catch (\Exception $e) {
            $this->logError('getMyTravelRequests', $e, ['user_id' => $user->id ?? null]);
            return back()->with('error', 'Terjadi kesalahan saat memuat data SPPD.');
        }
    }

    /**
     * AJAX: Daftar SPPD Saya (filter, search, pagination)
     */
    /**
     * Ajax endpoint untuk load data SPPD dengan validasi yang aman
     */
    public function ajaxList(Request $request)
    {
        try {
            // Validasi input dengan regex untuk mencegah injection
            $validated = $request->validate([
                'status' => 'nullable|string|in:' . implode(',', TravelRequestStatus::toArray()),
                'search' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\s\-_]+$/',
                'page' => 'nullable|integer|min:1|max:1000'
            ]);

            $user = Auth::user();
            
            // Delegate ke service untuk safe handling
            $travelRequests = $this->travelRequestService->getFilteredTravelRequests($validated, $user);

            return view('travel_requests.partials.my_requests_table', compact('travelRequests'))->render();

        } catch (\Exception $e) {
            $this->logError('ajaxList', $e, ['user_id' => Auth::id()]);
            return response()->json(['error' => 'Terjadi kesalahan saat memuat data.'], 500);
        }
    }

    public function create()
    {
        $user = auth()->user();
        $users = $this->getActiveUsersForSelection();
        return view('travel_requests.create', compact('user', 'users'));
    }

    public function store(TravelRequestStoreRequest $request)
    {
        try {
            DB::beginTransaction();

            // Buat SPPD baru
            $travelRequest = $this->travelRequestService->createTravelRequest($request, Auth::user());
            
            // Log SPPD yang dibuat untuk debugging
            $this->logBudgetDebug('store', $travelRequest);

            // Tambahkan peserta SPPD (jika ada)
            if ($request->filled('participants')) {
                $filteredParticipantIds = $this->parseAndFilterParticipants($request->participants);
                $travelRequest->participants()->sync($filteredParticipantIds);
            }

            // Upload dokumen pendukung (jika ada)
            $this->travelRequestService->handleDocumentUploads($travelRequest, $request, Auth::user());

            // Jika action = submit, generate kode SPPD dan inisialisasi approval workflow
            if ($request->action === 'submit') {
                $this->handleSubmission($travelRequest);
            }

            DB::commit();

            // Redirect ke halaman yang sesuai dengan pesan sukses
            $successMessage = $this->travelRequestService->getSuccessMessage($request->action ?? 'save');
            return redirect()->route('travel-requests.index')
                ->with('success', $successMessage)
                ->with('highlight_sppd', $travelRequest->id)
                ->with('just_submitted', $request->action === 'submit');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('store', $e, $request->except(['dokumen_pendukung']));
            return back()->withInput()->with('error', 'Gagal membuat pengajuan SPPD: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        // Jika parameter id = 'all', redirect ke daftar SPPD (indexAll)
        if ($id === 'all') {
            return redirect()->route('travel-requests.indexAll');
        }
        
        // Validasi: hanya boleh angka
        if (!$this->validateNumericId($id)) {
            abort(404, 'ID SPPD tidak valid.');
        }

        $currentUser = Auth::user();
        $travelRequest = TravelRequest::with(['user', 'participants'])->findOrFail($id);

        // IZINKAN AKSES JIKA: user adalah pengaju ATAU peserta ATAU role pimpinan/admin
        if (!$this->canAccessTravelRequest($travelRequest, $currentUser)) {
            abort(403, 'Unauthorized access to this travel request.');
        }

        return view('travel_requests.show', compact('travelRequest', 'currentUser'));
    }

    public function edit(string $id)
    {
        $travelRequest = TravelRequest::findOrFail($id);
        $user = auth()->user();
        $users = $this->getActiveUsersForSelection();
        return view('travel_requests.edit', compact('travelRequest', 'user', 'users'));
    }

    public function update(TravelRequestUpdateRequest $request, TravelRequest $travelRequest)
    {
        $validated = $request->validated();

        // Auto-submit: Set status to 'in_review' (no draft)
        $validated['status'] = 'in_review';
        
        // Log input biaya untuk debugging
        $this->logBudgetDebug('update', null, $request);

        try {
            DB::beginTransaction();
            
            // Update SPPD
            $travelRequest->update($validated);
            
            // Log SPPD yang diupdate untuk debugging
            $this->logBudgetDebug('update', $travelRequest);

            // Update peserta SPPD (pivot table)
            if (isset($validated['participants'])) {
                $filteredParticipantIds = $this->parseAndFilterParticipants($validated['participants']);
                $travelRequest->participants()->sync($filteredParticipantIds);
            }

            // Upload dokumen pendukung jika ada
            if ($request->hasFile('dokumen_pendukung')) {
                $this->travelRequestService->handleDocumentUploads($travelRequest, $request, Auth::user());
            }
            
            DB::commit();

            // Log activity and notify approvers
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'resubmit',
                'model_type' => 'TravelRequest',
                'model_id' => $travelRequest->id
            ]);

            if (class_exists('Notification') && method_exists($travelRequest, 'approvers')) {
                Notification::send($travelRequest->approvers, new SppdResubmitted($travelRequest));
            }

            return redirect()->route('travel-requests.index')->with('success', 'SPPD berhasil diajukan ulang!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating travel request: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['dokumen_pendukung']),
                'travel_request_id' => $travelRequest->id
            ]);
            return back()->withInput()->with('error', 'Gagal mengupdate SPPD: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $currentUser = Auth::user();

        // Hanya bisa hapus SPPD milik sendiri
        $travelRequest = TravelRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Hanya bisa hapus jika status in_review
        if ($travelRequest->status !== 'in_review') {
            return redirect()->route('my-travel-requests.index')
                ->with('error', 'Hanya SPPD dengan status in_review yang dapat dihapus.');
        }

        try {
            $travelRequest->delete();
            return redirect()->route('my-travel-requests.index')
                ->with('success', 'Pengajuan SPPD berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('my-travel-requests.index')
                ->with('error', 'Gagal menghapus SPPD: ' . $e->getMessage());
        }
    }

    public function submit($id)
    {
        $currentUser = Auth::user();
        if ($currentUser->role !== 'kasubbag') {
            abort(403, 'Hanya kasubbag yang dapat mengajukan SPPD.');
        }

        // Hanya bisa submit SPPD milik sendiri
        $travelRequest = TravelRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!in_array($travelRequest->status, ['in_review', 'revision'])) {
            return back()->with('error', 'Pengajuan tidak bisa diajukan ulang.');
        }

        // HAPUS update manual current_approval_level di sini, cukup lewat ApprovalService
        // $travelRequest->update([
        //     'status' => 'in_review',
        //     'submitted_at' => now(),
        //     'current_approval_level' => 1,
        // ]);
        $travelRequest->update([
            'status' => 'in_review',
            'submitted_at' => now(),
        ]);
        
        // Log SPPD status setelah update
        \Log::info('SPPD resubmitted:', [
            'id' => $travelRequest->id,
            'status' => $travelRequest->fresh()->status,
            'current_approval_level' => $travelRequest->fresh()->current_approval_level,
            'submitter_role' => $currentUser->role
        ]);
        
        // Inisialisasi approval workflow (JANGAN update current_approval_level manual di controller)
        $this->approvalService->initializeApprovalWorkflow($travelRequest->fresh());
        
        // Log SPPD status setelah initializeApprovalWorkflow
        \Log::info('SPPD after workflow initialization:', [
            'id' => $travelRequest->id,
            'status' => $travelRequest->fresh()->status,
            'current_approval_level' => $travelRequest->fresh()->current_approval_level
        ]);

        return redirect()->route('travel-requests.index')
            ->with('success', 'Pengajuan SPPD berhasil diajukan ulang.');
    }

    /**
     * Generate kode SPPD unik
     */
    /**
     * Get user scope based on role
     */
    private function getUserScope(): string
    {
        $currentUser = Auth::user();
        return in_array($currentUser->role, self::MANAGEMENT_ROLES) ? 'all' : 'my';
    }

    /**
     * Handle SPPD submission workflow
     */
    private function handleSubmission(TravelRequest $travelRequest): void
    {
        // Generate kode SPPD
        $kodeSppd = $this->travelRequestService->generateKodeSppd();
        $travelRequest->update(['kode_sppd' => $kodeSppd]);
        
        // Log SPPD submitted
        \Log::info('SPPD submitted:', [
            'id' => $travelRequest->id,
            'kode_sppd' => $kodeSppd,
            'status' => $travelRequest->status,
            'current_approval_level' => $travelRequest->current_approval_level,
            'submitter_role' => Auth::user()->role
        ]);
        
        // Inisialisasi approval workflow
        $this->approvalService->initializeApprovalWorkflow($travelRequest->fresh());
        
        // Log SPPD status setelah initializeApprovalWorkflow
        \Log::info('SPPD after workflow initialization:', [
            'id' => $travelRequest->id,
            'status' => $travelRequest->fresh()->status,
            'current_approval_level' => $travelRequest->fresh()->current_approval_level
        ]);
    }

    /**
     * Log error with context
     */
    private function logError(string $method, \Exception $e, array $context = []): void
    {
        \Log::error("Error in TravelRequestController::{$method}: " . $e->getMessage(), [
            'exception' => get_class($e),
            'trace' => $e->getTraceAsString(),
            'context' => $context
        ]);
    }

    /**
     * Export SPPD to PDF
     */
    public function exportPdf($id)
    {
        try {
            $travelRequest = TravelRequest::with(['user', 'approvals.approver'])->findOrFail($id);
            $currentUser = Auth::user();

            // Authorization checks
            if ($travelRequest->status !== TravelRequestStatus::COMPLETED->value) {
                abort(403, 'SPPD hanya bisa diunduh jika sudah disetujui dan statusnya completed.');
            }

            if (!$this->canAccessTravelRequest($travelRequest, $currentUser)) {
                abort(403, 'Unauthorized access to this travel request.');
            }

            // Get template type
            $template = \App\Models\TemplateDokumen::where('status_aktif', true)->first();
            $templateType = $template ? $template->tipe_file : 'fallback';

            // Process document using DocumentService
            return $this->documentService->processTemplate($travelRequest, $templateType);

        } catch (\Exception $e) {
            $this->logError('exportPdf', $e, ['travel_request_id' => $id]);
            return back()->with('error', 'Terjadi kesalahan saat export dokumen: ' . $e->getMessage());
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================

    /**
     * Parse and filter participants from request
     */
    private function parseAndFilterParticipants(string|array $participants): array
    {
        $participantIds = collect();
        
        if (is_string($participants)) {
            $participantIds = collect(explode(',', $participants))
                ->map(fn($id) => trim($id))
                ->filter(fn($id) => !empty($id) && is_numeric($id))
                ->map(fn($id) => (int)$id);
        } elseif (is_array($participants)) {
            if (count($participants) === 1 && is_string($participants[0]) && str_contains($participants[0], ',')) {
                $participantIds = collect(explode(',', $participants[0]))
                    ->map(fn($id) => trim($id))
                    ->filter(fn($id) => !empty($id) && is_numeric($id))
                    ->map(fn($id) => (int)$id);
            } else {
                $participantIds = collect($participants)
                    ->filter(fn($id) => !empty($id) && is_numeric($id))
                    ->map(fn($id) => (int)$id);
            }
        }

        return $participantIds->filter(function ($id) {
            $user = \App\Models\User::find($id);
            return $user && $user->role !== 'admin' && $user->id !== Auth::id();
        })->values()->all();
    }

    /**
     * Get active users for selection dropdowns
     */
    private function getActiveUsersForSelection(): Collection
    {
        return \App\Models\User::where('is_active', true)
            ->where('role', '!=', 'admin')
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get()
            ->map(function($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'role' => $u->role,
                    'avatar_url' => $u->avatar_url,
                ];
            })->values();
    }

    /**
     * Check if user can access travel request
     */
    private function canAccessTravelRequest(TravelRequest $travelRequest, User $user): bool
    {
        return $travelRequest->user_id === $user->id
            || $travelRequest->participants->contains('id', $user->id)
            || in_array($user->role, self::ALLOWED_ROLES_FOR_ACCESS);
    }

    /**
     * Log budget debug information
     */
    private function logBudgetDebug(string $method, ?TravelRequest $travelRequest = null, ?Request $request = null): void
    {
        if ($request) {
            \Log::debug("TravelRequestController::{$method} - Input biaya:", [
                'biaya_transport' => $request->biaya_transport,
                'biaya_penginapan' => $request->biaya_penginapan,
                'uang_harian' => $request->uang_harian,
                'biaya_lainnya' => $request->biaya_lainnya
            ]);
        }
        
        if ($travelRequest) {
            \Log::debug("TravelRequestController::{$method} - SPPD processed:", [
                'id' => $travelRequest->id,
                'biaya_transport' => $travelRequest->biaya_transport,
                'biaya_penginapan' => $travelRequest->biaya_penginapan,
                'uang_harian' => $travelRequest->uang_harian,
                'biaya_lainnya' => $travelRequest->biaya_lainnya,
                'total_biaya' => $travelRequest->total_biaya,
                'total_budget' => $travelRequest->total_budget
            ]);
        }
    }

    /**
     * Sanitize search input
     */
    private function sanitizeSearchInput(string $search): string
    {
        return trim(strip_tags($search));
    }

    /**
     * Validate numeric ID
     */
    private function validateNumericId(string $id): bool
    {
        return is_numeric($id) && (int)$id > 0;
    }


}
