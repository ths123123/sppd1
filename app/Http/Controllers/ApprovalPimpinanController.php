<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelRequest;
use App\Models\Approval;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ApprovalPimpinanController extends Controller
{
    protected $approvalService;
    protected $notificationService;

    public function __construct(ApprovalService $approvalService, NotificationService $notificationService)
    {
        $this->approvalService = $approvalService;
        $this->notificationService = $notificationService;
    }

    /**
     * Build common approval query with proper security measures
     */
    private function buildApprovalQuery(Request $request, User $user)
    {
        $query = TravelRequest::with(['user', 'approvals.approver'])
            ->where('status', 'in_review');
            
        // Role-based filtering with proper authorization
        if (in_array($user->role, ['sekretaris', 'ppk'])) {
            if ($user->role === 'sekretaris') {
                $query->where('current_approval_level', 1);
            } elseif ($user->role === 'ppk') {
                $query->where('current_approval_level', 2);
            }
        } else {
            // Admin can view all, others get blocked
            if ($user->role !== 'admin') {
                abort(403, 'Hanya sekretaris, ppk, atau admin yang dapat mengakses menu approval.');
            }
        }

        // Urgency filter with proper date handling
        if ($request->filled('urgency')) {
            if ($request->urgency === 'urgent') {
                $query->whereDate('tanggal_berangkat', '<=', now()->addDays(3)->toDateString());
            } elseif ($request->urgency === 'normal') {
                $query->whereDate('tanggal_berangkat', '>', now()->addDays(3)->toDateString());
            }
        }

        // Search filter with proper sanitization and SQL injection prevention
        if ($request->filled('search')) {
            $search = $this->sanitizeSearchInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('tujuan', 'ILIKE', "%{$search}%")
                  ->orWhere('keperluan', 'ILIKE', "%{$search}%")
                  ->orWhere('kode_sppd', 'ILIKE', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at');
    }

    /**
     * Sanitize search input to prevent SQL injection and XSS
     */
    private function sanitizeSearchInput($input)
    {
        // Remove dangerous characters and limit length
        $sanitized = preg_replace('/[^\w\s\-\.\,\/]/', '', $input);
        return substr($sanitized, 0, 100); // Limit to 100 characters
    }

    /**
     * Unified response handler for API and web requests
     */
    private function handleResponse(Request $request, $success, $message, $redirectRoute = null, $data = null)
    {
        $isApiRequest = $request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json';
        
        if ($isApiRequest) {
            $response = ['success' => $success, 'message' => $message];
            if ($data) {
                $response['data'] = $data;
            }
            return response()->json($response, $success ? 200 : 422);
        }
        
        if ($redirectRoute) {
            return redirect()->route($redirectRoute)->with($success ? 'success' : 'error', $message);
        }
        
        return redirect()->back()->with($success ? 'success' : 'error', $message);
    }

    /**
     * Unified validation for approval operations
     */
    private function validateApprovalRequest(Request $request, array $rules, array $messages = [])
    {
        $isApiRequest = $request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json';
        
        if ($isApiRequest) {
            // Manual validation for API requests
            $validator = Validator::make($request->all(), $rules, $messages);
            
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            
            return $validator->validated();
        }
        
        // Standard validation for web requests
        return $request->validate($rules, $messages);
    }

    /**
     * Secure exception handling with proper logging
     */
    private function handleException(\Exception $e, Request $request, $operation = 'unknown')
    {
        Log::error("Approval operation failed: {$operation}", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => Auth::id(),
            'request_data' => $request->except(['password', 'token'])
        ]);

        $isApiRequest = $request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json';
        
        if ($isApiRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
        
        return redirect()->back()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
    }

    /**
     * Ajukan revisi pengajuan SPPD dengan validasi dan keamanan yang ditingkatkan
     */
    public function revision(Request $request, $id)
    {
        // Authorization check
        if (Auth::user()->role === 'admin') {
            abort(403, 'Admin tidak boleh melakukan revisi SPPD.');
        }

        try {
            // Validate input with proper rules
            $validatedData = $this->validateApprovalRequest($request, [
                'revision_reason' => 'required|min:10|max:1000|string|regex:/^[a-zA-Z0-9\s\.\,\-\_\(\)\:\;]+$/',
                'target' => 'required|in:kasubbag',
            ], [
                'revision_reason.required' => 'Alasan revisi wajib diisi.',
                'revision_reason.min' => 'Alasan revisi minimal 10 karakter.',
                'revision_reason.max' => 'Alasan revisi maksimal 1000 karakter.',
                'revision_reason.regex' => 'Alasan revisi mengandung karakter yang tidak diizinkan.',
                'target.required' => 'Target revisi wajib dipilih.',
                'target.in' => 'Target revisi tidak valid.',
            ]);

            $travelRequest = TravelRequest::findOrFail($id);
            $user = Auth::user();

            // Additional authorization check
            if (!$this->canUserModifyRequest($travelRequest, $user)) {
                return $this->handleResponse($request, false, 'Anda tidak memiliki izin untuk melakukan revisi pada SPPD ini.');
            }

            $success = $this->approvalService->processTravelRequestRevision(
                $travelRequest,
                $user,
                $validatedData['revision_reason'],
                $validatedData['target']
            );

            if ($success) {
                $this->notificationService->notifySppdRevision($travelRequest, $user, $validatedData['revision_reason']);
                return $this->handleResponse($request, true, 'Pengajuan telah dikembalikan untuk revisi.', 'approval.pimpinan.index');
            }

            return $this->handleResponse($request, false, 'Gagal memproses permintaan revisi.');

        } catch (ValidationException $e) {
            $isApiRequest = $request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json';
            
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return $this->handleException($e, $request, 'revision');
        }
    }

    /**
     * Tampilkan semua pengajuan SPPD yang perlu disetujui dengan query yang aman
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        try {
            $query = $this->buildApprovalQuery($request, $user);
            $requests = $query->paginate(10);

            if ($request->ajax()) {
                return view('approval.pimpinan.partials.approval_requests_table', compact('requests'))->render();
            }
            
            return view('approval.pimpinan.approval_requests', compact('requests'));
        } catch (\Exception $e) {
            return $this->handleException($e, $request, 'index');
        }
    }

    /**
     * AJAX: Filter & pagination approval requests dengan query yang aman
     */
    public function ajaxListApproval(Request $request)
    {
        $user = Auth::user();
        
        try {
            $query = $this->buildApprovalQuery($request, $user);
            $requests = $query->paginate(10);
            
            return view('approval.pimpinan.partials.approval_requests_table', compact('requests'))->render();
        } catch (\Exception $e) {
            return $this->handleException($e, $request, 'ajax_list');
        }
    }

    /**
     * Check if user can modify the travel request
     */
    private function canUserModifyRequest(TravelRequest $travelRequest, User $user)
    {
        // Admin can modify any request
        if ($user->role === 'admin') {
            return true;
        }

        // Check if user is in the approval flow for this request
        if (in_array($user->role, ['sekretaris', 'ppk'])) {
            if ($user->role === 'sekretaris' && $travelRequest->current_approval_level === 1) {
                return true;
            }
            if ($user->role === 'ppk' && $travelRequest->current_approval_level === 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tampilkan detail pengajuan untuk approval dengan authorization yang ketat
     */
    public function show($id)
    {
        try {
            $travelRequest = TravelRequest::with(['user', 'approvals.approver', 'documents'])
                ->findOrFail($id);

            $user = Auth::user();

            // Strict authorization check
            if (!$this->canUserModifyRequest($travelRequest, $user)) {
                abort(403, 'Anda tidak memiliki akses untuk melihat pengajuan ini.');
            }

            return view('approval.pimpinan.show', compact('travelRequest'));

        } catch (\Exception $e) {
            Log::error('Error loading travel request detail: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat detail pengajuan.');
        }
    }

    /**
     * Setujui pengajuan SPPD dengan validasi dan keamanan yang ditingkatkan
     */
    public function approve(Request $request, $id)
    {
        // Authorization check
        if (Auth::user()->role === 'admin') {
            abort(403, 'Admin tidak boleh melakukan approval SPPD.');
        }

        try {
            $validatedData = $this->validateApprovalRequest($request, [
                'comments' => 'nullable|string|max:500|regex:/^[a-zA-Z0-9\s\.\,\-\_\(\)\:\;]+$/',
                'plt_name' => 'nullable|string|max:100|regex:/^[a-zA-Z\s\.]+$/',
            ], [
                'comments.max' => 'Komentar maksimal 500 karakter.',
                'comments.regex' => 'Komentar mengandung karakter yang tidak diizinkan.',
                'plt_name.max' => 'Nama PLT maksimal 100 karakter.',
                'plt_name.regex' => 'Nama PLT hanya boleh berisi huruf, spasi, dan titik.',
            ]);

            $travelRequest = TravelRequest::with('participants')->findOrFail($id);
            $user = Auth::user();

            // Check if user can modify this request
            if (!$this->canUserModifyRequest($travelRequest, $user)) {
                return $this->handleResponse($request, false, 'Anda tidak memiliki izin untuk melakukan approval pada SPPD ini.');
            }

            // Check if approver is also participant
            $isApproverParticipant = $travelRequest->participants->contains('id', $user->id);
            if ($isApproverParticipant && empty($validatedData['plt_name'])) {
                return $this->handleResponse($request, false, 'Anda adalah peserta SPPD ini. Approval harus dilakukan oleh Plt/Plh. Silakan masukkan nama Plt/Plh.');
            }

            // Prepare comments with PLT information
            $finalComments = $validatedData['comments'] ?? '';
            if ($isApproverParticipant && !empty($validatedData['plt_name'])) {
                $finalComments = '[PLT/PLH: ' . $validatedData['plt_name'] . '] ' . $finalComments;
            }

            $success = $this->approvalService->processTravelRequestApproval(
                $travelRequest,
                $user,
                $finalComments
            );

            if ($success) {
                $this->notificationService->notifySppdCompleted($travelRequest, $user);
                return $this->handleResponse($request, true, 'SPPD berhasil disetujui.', 'approval.pimpinan.index');
            }

            return $this->handleResponse($request, false, 'Gagal memproses approval.');

        } catch (ValidationException $e) {
            $isApiRequest = $request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json';
            
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return $this->handleException($e, $request, 'approve');
        }
    }

    /**
     * Tolak pengajuan SPPD dengan validasi dan keamanan yang ditingkatkan
     */
    public function reject(Request $request, $id)
    {
        // Authorization check
        if (Auth::user()->role === 'admin') {
            abort(403, 'Admin tidak boleh melakukan reject SPPD.');
        }

        try {
            $validatedData = $this->validateApprovalRequest($request, [
                'rejection_reason' => 'required|min:10|max:1000|string|regex:/^[a-zA-Z0-9\s\.\,\-\_\(\)\:\;]+$/',
            ], [
                'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
                'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
                'rejection_reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
                'rejection_reason.regex' => 'Alasan penolakan mengandung karakter yang tidak diizinkan.',
            ]);

            $travelRequest = TravelRequest::findOrFail($id);
            $user = Auth::user();

            // Check if user can modify this request
            if (!$this->canUserModifyRequest($travelRequest, $user)) {
                return $this->handleResponse($request, false, 'Anda tidak memiliki izin untuk melakukan penolakan pada SPPD ini.');
            }

            $success = $this->approvalService->processTravelRequestRejection(
                $travelRequest,
                $user,
                $validatedData['rejection_reason']
            );

            if ($success) {
                $this->notificationService->notifySppdRejected($travelRequest, $user, $validatedData['rejection_reason']);
                return $this->handleResponse($request, true, 'Pengajuan telah ditolak.', 'approval.pimpinan.index');
            }

            return $this->handleResponse($request, false, 'Gagal memproses penolakan.');

        } catch (ValidationException $e) {
            $isApiRequest = $request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json';
            
            if ($isApiRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return $this->handleException($e, $request, 'reject');
        }
    }

    /**
     * Calculate average approval time in hours
     */
    private function calculateAverageApprovalTime()
    {
        $completedRequests = TravelRequest::where('status', 'completed')
                                        ->whereBetween('created_at', [now()->subMonth(), now()])
                                        ->get();

        if ($completedRequests->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        $count = 0;

        foreach ($completedRequests as $request) {
            $lastApproval = $request->approvals()->orderBy('created_at', 'desc')->first();
            if ($lastApproval) {
                $hours = $request->created_at->diffInHours($lastApproval->created_at);
                $totalHours += $hours;
                $count++;
            }
        }

        return $count > 0 ? round($totalHours / $count, 1) : 0;
    }

    /**
     * Dashboard statistik approval
     */
    public function dashboard()
    {
        $user = Auth::user();

        try {
            $stats = $this->approvalService->getApprovalDashboardStats($user->role);
            return view('approval.pimpinan.dashboard', compact('stats'));

        } catch (\Exception $e) {
            Log::error('Error loading approval dashboard: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard approval.');
        }
    }

    /**
     * Tampilkan riwayat approval
     */
    public function history($id)
    {
        try {
            $travelRequest = TravelRequest::with(['approvals.approver'])
                ->findOrFail($id);

            $user = Auth::user();
            
            // Authorization check
            if (!$this->canUserModifyRequest($travelRequest, $user)) {
                abort(403, 'Anda tidak memiliki akses untuk melihat riwayat pengajuan ini.');
            }

            $approvals = $travelRequest->approvals()
                ->with('approver')
                ->orderBy('level')
                ->get();

            return view('approval.pimpinan.history', compact('travelRequest', 'approvals'));
        } catch (\Exception $e) {
            Log::error('Error loading approval history: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat riwayat approval.');
        }
    }

    /**
     * Fix inconsistent data in travel requests - Admin only
     */
    public function fixInconsistentData()
    {
        // Strict admin authorization
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Hanya admin yang dapat menjalankan fungsi ini.');
        }
        
        try {
            // Fix SPPD with inconsistent approval levels
            $inconsistentRequests = TravelRequest::where('status', 'in_review')
                ->where(function($query) {
                    $query->whereNull('current_approval_level')
                          ->orWhere('current_approval_level', 0)
                          ->orWhere('current_approval_level', '>', 2);
                })
                ->get();
                
            $count = 0;
            
            foreach ($inconsistentRequests as $request) {
                $approvals = $request->approvals()->orderBy('level', 'desc')->first();
                
                if ($approvals) {
                    $request->current_approval_level = $approvals->level + 1;
                } else {
                    $request->current_approval_level = 1;
                }
                
                $request->save();
                $count++;
            }
            
            // Fix missing approver roles
            $missingRoleRequests = TravelRequest::where('status', 'in_review')
                ->whereIn('current_approval_level', [1, 2])
                ->get()
                ->filter(function($item) {
                    return $item->current_approver_role === null;
                });
                
            foreach ($missingRoleRequests as $request) {
                $this->approvalService->initializeApprovalWorkflow($request);
                $count++;
            }
            
            return redirect()->back()->with('success', "Berhasil memperbaiki {$count} data SPPD yang tidak konsisten.");
        } catch (\Exception $e) {
            Log::error('Error fixing inconsistent data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbaiki data.');
        }
    }
}
