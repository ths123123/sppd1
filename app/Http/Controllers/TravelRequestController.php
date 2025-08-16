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
use App\Services\ParticipantService;
use App\Enums\TravelRequestStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TemplateDokumen;

class TravelRequestController extends Controller
{
    use AuthorizesRequests, BudgetCalculationTrait;

    // Constants
    private const ALLOWED_ROLES_FOR_ACCESS = ['kasubbag', 'sekretaris', 'ppk', 'admin'];
    private const MANAGEMENT_ROLES = ['kasubbag', 'sekretaris', 'ppk'];
    private const ITEMS_PER_PAGE = 10;
    private const PENDING_APPROVALS_LIMIT = 5;

    protected TravelRequestService $travelRequestService;
    protected ApprovalService $approvalService;
    protected CalculationService $calculationService;
    protected NotificationService $notificationService;
    protected DocumentService $documentService;
    protected ParticipantService $participantService;

    public function __construct(
        TravelRequestService $travelRequestService,
        ApprovalService $approvalService,
        CalculationService $calculationService,
        NotificationService $notificationService,
        DocumentService $documentService,
        ParticipantService $participantService
    ) {
        $this->travelRequestService = $travelRequestService;
        $this->approvalService = $approvalService;
        $this->calculationService = $calculationService;
        $this->notificationService = $notificationService;
        $this->documentService = $documentService;
        $this->participantService = $participantService;
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
            $query = TravelRequest::query();
            $query = $this->travelRequestService->filterTravelRequests($validated, $query);
            $travelRequests = $query->where('user_id', $user->id)->get();

            return view('travel_requests.partials.my_requests_table', compact('travelRequests'))->render();

        } catch (\Exception $e) {
            $this->logError('ajaxList', $e, ['user_id' => Auth::id()]);
            return response()->json(['error' => 'Terjadi kesalahan saat memuat data.'], 500);
        }
    }

    public function create()
    {
        $user = Auth::user();
        $users = $this->participantService->getAvailableUsers();
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

            // Tambahkan peserta SPPD menggunakan service yang robust
            $this->participantService->syncParticipants($travelRequest, $request->participants);

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

        // Refresh relasi participants untuk memastikan data ter-update
        $travelRequest->load('participants');

        // IZINKAN AKSES JIKA: user adalah pengaju ATAU peserta ATAU role pimpinan/admin
        if (!$this->canAccessTravelRequest($travelRequest, $currentUser)) {
            abort(403, 'Unauthorized access to this travel request.');
        }

        return view('travel_requests.show', compact('travelRequest', 'currentUser'));
    }

    public function edit(string $id)
    {
        $travelRequest = TravelRequest::with(['participants', 'user'])->findOrFail($id);
        $user = Auth::user();
        $users = $this->participantService->getAvailableUsers();
        return view('travel_requests.edit', compact('travelRequest', 'user', 'users'));
    }

    public function update(Request $request, TravelRequest $travelRequest)
    {
        // Debug: Log semua data yang diterima
        Log::info('Update SPPD - Raw request data:', [
            'all_data' => $request->all(),
            'participants' => $request->input('participants'),
            'participants_array' => $request->input('participants', []),
            'has_participants' => $request->has('participants'),
            'participants_count' => count($request->input('participants', []))
        ]);

        // Validasi manual karena TravelRequestUpdateRequest tidak ada
        $validated = $request->validate([
            'tujuan' => 'required|string|max:255',
            'keperluan' => 'required|string|max:500',
            'tanggal_berangkat' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_berangkat',
            'transportasi' => 'required|string|max:100',
            'tempat_berangkat' => 'required|string|max:255',
            'tempat_menginap' => 'nullable|string|max:255',
            'biaya_transport' => 'nullable|numeric|min:0',
            'biaya_penginapan' => 'nullable|numeric|min:0',
            'uang_harian' => 'nullable|numeric|min:0',
            'biaya_lainnya' => 'nullable|numeric|min:0',
            'total_biaya' => 'nullable|numeric|min:0',
            'sumber_dana' => 'nullable|string|max:100',
            'catatan_pemohon' => 'nullable|string|max:1000',
            'participants' => 'nullable|array',
            'participants.*' => 'nullable|integer|exists:users,id'
        ]);

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

            // Debug: Log data peserta sebelum sync
            Log::info('Update SPPD - Before sync participants:', [
                'validated_participants' => $validated['participants'] ?? null,
                'participants_type' => gettype($validated['participants'] ?? null),
                'participants_count' => is_array($validated['participants'] ?? null) ? count($validated['participants']) : 0,
                'raw_participants' => $request->input('participants'),
                'all_participants_inputs' => $request->all('participants')
            ]);

            // Update peserta SPPD menggunakan service yang robust
            $this->participantService->syncParticipants($travelRequest, $validated['participants'] ?? null);

            // Jika status sebelumnya revisi, inisialisasi workflow revisi
            if ($travelRequest->getOriginal('status') === 'revision') {
                app(\App\Services\ApprovalService::class)->initializeApprovalWorkflowAfterRevision($travelRequest);
            }

            // Debug: Log setelah sync
            Log::info('Update SPPD - After sync participants:', [
                'travel_request_id' => $travelRequest->id,
                'participants_count_after_sync' => $travelRequest->participants()->count(),
                'participants_after_sync' => $travelRequest->participants()->pluck('name')->toArray()
            ]);

            // Upload dokumen pendukung jika ada
            if ($request->hasFile('dokumen_pendukung')) {
                $this->travelRequestService->handleDocumentUploads($travelRequest, $request, Auth::user());
            }

            DB::commit();

            // Log activity and notify approvers
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'resubmit',
                'model_type' => 'TravelRequest',
                'model_id' => $travelRequest->id,
                'details' => [
                    'kode_sppd' => $travelRequest->kode_sppd,
                    'tujuan' => $travelRequest->tujuan,
                    'description' => "📋 SPPD {$travelRequest->kode_sppd} telah berhasil diajukan ulang untuk tujuan {$travelRequest->tujuan} oleh " . Auth::user()->name . " setelah proses revisi.",
                ]
            ]);

            if (class_exists('Notification') && method_exists($travelRequest, 'approvers')) {
                Notification::send($travelRequest->approvers, new SppdResubmitted($travelRequest));
            }

            return redirect()->route('travel-requests.index')->with('success', 'SPPD berhasil diajukan ulang!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating travel request: ' . $e->getMessage(), [
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

        try {
            DB::beginTransaction();

            // Debug: Log request data
            Log::info('Submit SPPD - Request data:', [
                'travel_request_id' => $travelRequest->id,
                'request_all' => request()->all(),
                'participants_from_request' => request()->input('participants'),
                'participants_count' => count(request()->input('participants', []))
            ]);

            // PERBAIKAN: Gunakan data dari request untuk memperbarui SPPD
            // Dapatkan data SPPD terbaru dari database
            $travelRequestData = $travelRequest->toArray();

            // Gabungkan dengan data dari request
            $requestData = request()->all();
            $mergedData = array_merge($travelRequestData, $requestData);

            // Buat request baru dengan data gabungan
            $updatedRequest = new Request($mergedData);
            $updatedRequest->merge([
                'action' => 'submit',
                'status' => 'in_review',
                'submitted_at' => now(),
            ]);

            // Update SPPD menggunakan service
            $this->travelRequestService->updateTravelRequest($travelRequest, $updatedRequest);

            // Debug: Log data peserta sebelum sync
            Log::info('Submit SPPD - Current participants before sync:', [
                'travel_request_id' => $travelRequest->id,
                'current_participants_count' => $travelRequest->participants()->count(),
                'current_participants' => $travelRequest->participants()->pluck('name')->toArray()
            ]);

            // Gunakan data peserta dari request jika ada, jika tidak gunakan data yang sudah ada
            $participantsToSync = request()->input('participants');
            if (empty($participantsToSync)) {
                $participantsToSync = $travelRequest->participants()->pluck('user_id')->toArray();
                Log::info('Submit SPPD - No participants in request, using existing data:', [
                    'participants' => $participantsToSync
                ]);
            } else {
                Log::info('Submit SPPD - Using participants from request:', [
                    'participants' => $participantsToSync
                ]);
            }

            // Sync participants dengan data yang benar
            $this->participantService->syncParticipants($travelRequest, $participantsToSync);

            // Debug: Log data peserta setelah sync
            Log::info('Submit SPPD - After sync participants:', [
                'travel_request_id' => $travelRequest->id,
                'participants_count_after_sync' => $travelRequest->participants()->count(),
                'participants_after_sync' => $travelRequest->participants()->pluck('name')->toArray()
            ]);

            DB::commit();

            // Log SPPD status setelah update
            Log::info('SPPD resubmitted:', [
                'id' => $travelRequest->id,
                'status' => $travelRequest->fresh()->status,
                'current_approval_level' => $travelRequest->fresh()->current_approval_level,
                'submitter_role' => $currentUser->role
            ]);

            // Gunakan handleSubmission untuk menentukan metode inisialisasi yang tepat
            // Pastikan menggunakan metode yang benar untuk pengajuan ulang setelah revisi
            Log::info('Calling handleSubmission for SPPD ID: ' . $travelRequest->id . ' with status: ' . $travelRequest->status);
            $this->handleSubmission($travelRequest->fresh());

            // Log SPPD status setelah initializeApprovalWorkflow
            Log::info('SPPD after workflow initialization:', [
                'id' => $travelRequest->id,
                'status' => $travelRequest->fresh()->status,
                'current_approval_level' => $travelRequest->fresh()->current_approval_level
            ]);

            return redirect()->route('travel-requests.index')
                ->with('success', 'Pengajuan SPPD berhasil diajukan ulang.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting travel request: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'travel_request_id' => $travelRequest->id
            ]);
            return back()->with('error', 'Gagal mengajukan ulang SPPD: ' . $e->getMessage());
        }
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
        // Log SPPD submitted
        Log::info('SPPD submitted:', [
            'id' => $travelRequest->id,
            'status' => $travelRequest->status,
            'current_approval_level' => $travelRequest->current_approval_level,
            'submitter_role' => Auth::user()->role
        ]);

        // Check if this is a resubmission after revision
        $hasPreviousApprovals = $travelRequest->approvals()->exists();

        if ($hasPreviousApprovals) {
            // Use smart workflow initialization for resubmission
            Log::info('Using initializeApprovalWorkflowAfterRevision for SPPD ID: ' . $travelRequest->id);
            $this->approvalService->initializeApprovalWorkflowAfterRevision($travelRequest->fresh());
        } else {
            // Use normal workflow initialization for new submission
            Log::info('Using initializeApprovalWorkflow for SPPD ID: ' . $travelRequest->id);
            $this->approvalService->initializeApprovalWorkflow($travelRequest->fresh());
        }

        // Kirim notifikasi ke approver dan pemohon
        try {
            Log::info('Sending notifications for SPPD ID: ' . $travelRequest->id);
            $this->notificationService->notifySppdSubmitted($travelRequest->fresh());
        } catch (\Exception $e) {
            Log::error('Failed to send notifications: ' . $e->getMessage());
        }

        // Log SPPD status setelah initializeApprovalWorkflow
        Log::info('SPPD after workflow initialization:', [
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
        Log::error("Error in TravelRequestController::{$method}: " . $e->getMessage(), [
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
            $template = TemplateDokumen::where('status_aktif', true)->first();
            $templateType = $template ? $template->tipe_file : 'fallback';

            // Process document using DocumentService
            return $this->documentService->processTemplate($travelRequest, $templateType);

        } catch (\Exception $e) {
            $this->logError('exportPdf', $e, ['travel_request_id' => $id]);
            return back()->with('error', 'Terjadi kesalahan saat export dokumen: ' . $e->getMessage());
        }
    }

    public function downloadApprovalLetter($id)
    {
        try {
            $travelRequest = TravelRequest::with(['user', 'approvals.approver'])->findOrFail($id);
            $currentUser = Auth::user();
            if ($travelRequest->status !== TravelRequestStatus::COMPLETED->value) {
                abort(403, 'Surat tugas hanya bisa diunduh jika status sudah completed.');
            }
            if (!$this->canAccessTravelRequest($travelRequest, $currentUser)) {
                abort(403, 'Unauthorized access to this travel request.');
            }
            // Data for the approval letter
            $pegawai = $travelRequest->user;
            $approval = $travelRequest->approvals->where('role', 'sekretaris')->first();
            $data = [
                'travelRequest' => $travelRequest,
                'pegawai' => $pegawai,
                'approval' => $approval,
            ];
            $pdf = Pdf::loadView('travel_requests.partials.surat_persetujuan', $data);
            return $pdf->download('Surat-Tugas-' . ($pegawai->name ?? 'pegawai') . '.pdf');
        } catch (\Exception $e) {
            $this->logError('downloadApprovalLetter', $e, ['travel_request_id' => $id]);
            return back()->with('error', 'Terjadi kesalahan saat export surat tugas: ' . $e->getMessage());
        }
    }

    // ==================== PRIVATE HELPER METHODS ====================



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
            Log::debug("TravelRequestController::{$method} - Input biaya:", [
                'biaya_transport' => $request->biaya_transport,
                'biaya_penginapan' => $request->biaya_penginapan,
                'uang_harian' => $request->uang_harian,
                'biaya_lainnya' => $request->biaya_lainnya
            ]);
        }

        if ($travelRequest) {
            Log::debug("TravelRequestController::{$method} - SPPD processed:", [
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
