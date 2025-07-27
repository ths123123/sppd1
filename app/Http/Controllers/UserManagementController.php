<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;

class UserManagementController extends Controller
{
    protected $userManagementService;

    public function __construct(UserManagementService $userManagementService)
    {
        $this->userManagementService = $userManagementService;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'role' => $request->get('role'),
                'status' => $request->get('status'),
                'search' => $request->get('search'),
            ];

            $users = $this->userManagementService->getUsers($filters);
            $stats = $this->userManagementService->getUserStatistics();

            return view('users.list', compact('users', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error loading users: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data user.');
        }
    }

    public function listJson(Request $request)
    {
        try {
            $filters = [
                'role' => $request->get('role'),
                'status' => $request->get('status'),
                'search' => $request->get('search'),
            ];
            $users = $this->userManagementService->getUsers($filters);
            $html = view('users.partials.table-rows', compact('users'))->render();
            return response()->json([
                'html' => $html,
                'last_updated' => now()->format('d M Y, H:i'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading users (AJAX): ' . $e->getMessage());
            return response()->json([
                'html' => '<tr><td colspan="7" class="text-center py-16"><div class="flex flex-col items-center"><i class="fas fa-exclamation-triangle text-3xl text-red-400 mb-2"></i><h3 class="text-base font-semibold text-gray-900">Gagal Memuat Data</h3><p class="text-gray-500 text-sm">Terjadi kesalahan saat mengambil data pengguna.</p></div></td></tr>',
                'last_updated' => now()->format('d M Y, H:i'),
            ], 500);
        }
    }

    public function toggleStatus(User $user)
    {
        try {
            $this->userManagementService->toggleUserStatus($user);

            $status = $user->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil {$status}",
                'status' => $user->fresh()->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $rules = $this->userManagementService->validateUserData($request->all());
            $validatedData = $request->validate($rules);

            $user = $this->userManagementService->createUser($validatedData);

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil ditambahkan"
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'pdf');
            $filters = [
                'role' => $request->get('role'),
                'status' => $request->get('status'),
                'search' => $request->get('search'),
            ];

            $users = $this->userManagementService->getUsersForExport($filters);

            switch ($format) {
                case 'pdf':
                    return $this->exportToPdf($users);
                default:
                    return $this->exportToPdf($users);
            }

        } catch (\Exception $e) {
            Log::error('Error exporting users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat export data'
            ], 500);
        }
    }

    private function exportToExcel($users)
    {
        $filename = 'users_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new UsersExport($users), $filename);
    }

    private function exportToPdf($users)
    {
        $data = [
            'title' => 'Daftar User SPPD KPU Kabupaten Cirebon',
            'users' => $users,
            'exported_at' => now()->format('d/m/Y H:i:s'),
            'exported_by' => auth()->user()->name
        ];

        $pdf = Pdf::loadView('exports.users-pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('users_' . date('Y-m-d_His') . '.pdf');
    }

    private function exportToWord($users)
    {
        $data = [
            'title' => 'Daftar User SPPD KPU Kabupaten Cirebon',
            'users' => $users,
            'exported_at' => now()->format('d/m/Y H:i:s'),
            'exported_by' => auth()->user()->name
        ];

        $content = view('exports.users-word', $data)->render();

        $headers = [
            'Content-type' => 'application/vnd.ms-word',
            'Content-Disposition' => 'attachment;Filename=users_' . date('Y-m-d_His') . '.doc'
        ];

        return response($content, 200, $headers);
    }
}
