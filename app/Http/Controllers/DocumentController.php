<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\TravelRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Cek apakah user adalah sekretaris, kasubbag, atau ppk
        $canViewAll = in_array($user->role, ['sekretaris', 'kasubbag', 'ppk', 'admin']);

        return view('documents.list', compact('canViewAll'));
    }

    // Dokumen SPPD milik user sendiri
    public function myDocuments()
    {
        $user = Auth::user();

        // Ambil dokumen dari SPPD yang dibuat oleh user sendiri
        $documents = Document::whereHas('travelRequest', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['travelRequest', 'uploader'])->orderByDesc('created_at')->paginate(20);

        return view('documents.my-documents', compact('documents'));
    }

    // Rekap seluruh dokumen (hanya untuk sekretaris, kasubbag, dan ppk)
    public function allDocuments()
    {

        $user = Auth::user();
        // Cek akses sesuai middleware (role di field users)
        if (!in_array($user->role, ['sekretaris', 'kasubbag', 'ppk', 'admin'])) {
            abort(403, 'Akses ditolak. Hanya sekretaris, kasubbag, dan ppk yang dapat mengakses halaman ini.');
        }

        // Ambil semua dokumen dengan informasi pengaju
        $documents = Document::with(['travelRequest.user', 'uploader'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('documents.rekap-dokumen', compact('documents'));
    }

    /**
     * Download dokumen
     */
    public function download(Document $document)
    {
        $user = Auth::user();
        
        // Cek akses dokumen
        $canAccess = $this->canAccessDocument($user, $document);
        
        if (!$canAccess) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Cek apakah file ada
        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        // Download file
        return Storage::disk('local')->download($document->file_path, $document->original_filename);
    }

    /**
     * Hapus dokumen
     */
    public function destroy(Document $document)
    {
        $user = Auth::user();
        
        // Cek akses dokumen
        $canAccess = $this->canAccessDocument($user, $document);
        
        if (!$canAccess) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Hanya creator atau admin yang bisa hapus
        if ($document->uploaded_by !== $user->id && $user->role !== 'admin') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus dokumen ini.');
        }

        try {
            // Hapus file dari storage
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            // Hapus record dari database
            $document->delete();

            return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Cek apakah user dapat mengakses dokumen
     */
    private function canAccessDocument($user, $document)
    {
        // Admin dapat akses semua dokumen
        if ($user->role === 'admin') {
            return true;
        }

        // Creator dapat akses dokumennya sendiri
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        // Pimpinan (kasubbag, sekretaris, ppk) dapat akses dokumen terkait SPPD
        if (in_array($user->role, ['kasubbag', 'sekretaris', 'ppk'])) {
            return true;
        }

        // Staff hanya dapat akses dokumen dari SPPD yang mereka buat
        if ($user->role === 'staff' && $document->travelRequest->user_id === $user->id) {
            return true;
        }

        return false;
    }
}
