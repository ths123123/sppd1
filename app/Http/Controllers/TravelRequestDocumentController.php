<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelRequest;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TravelRequestDocumentController extends Controller
{
    /**
     * Upload dokumen pendukung untuk travel request
     */
    public function uploadSupportingDocuments(Request $request, TravelRequest $travelRequest)
    {
        // Validasi request
        $request->validate([
            'dokumen_pendukung' => 'required|array',
            'dokumen_pendukung.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // max 5MB
        ]);

        // Cek akses
        if ($travelRequest->user_id !== Auth::id() && !in_array(Auth::user()->role, ['admin', 'kasubbag', 'sekretaris', 'ppk'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengunggah dokumen pada SPPD ini.');
        }

        $uploadedCount = 0;

        foreach ($request->file('dokumen_pendukung') as $file) {
            $filename = uniqid('doc_') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/documents', $filename);

            Document::create([
                'travel_request_id' => $travelRequest->id,
                'uploaded_by' => Auth::id(),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'document_type' => 'supporting',
                'description' => 'Dokumen pendukung SPPD',
                'is_required' => false,
                'is_verified' => false,
            ]);

            $uploadedCount++;
        }

        return redirect()->back()->with('success', "$uploadedCount dokumen pendukung berhasil diunggah.");
    }

    /**
     * Upload laporan perjalanan dinas untuk travel request
     */
    public function uploadTravelReports(Request $request, TravelRequest $travelRequest)
    {
        // Validasi request
        $request->validate([
            'laporan_perjalanan' => 'required|array',
            'laporan_perjalanan.*' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // max 5MB
        ]);

        // Cek akses
        if ($travelRequest->user_id !== Auth::id() && !in_array(Auth::user()->role, ['admin', 'kasubbag', 'sekretaris', 'ppk'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengunggah dokumen pada SPPD ini.');
        }

        // Cek apakah perjalanan sudah selesai
        $today = Carbon::today();
        $endDate = Carbon::parse($travelRequest->tanggal_kembali);
        
        if ($today->lt($endDate)) {
            return redirect()->back()->with('error', 'Laporan perjalanan dinas hanya dapat diunggah setelah tanggal perjalanan selesai.');
        }

        $uploadedCount = 0;

        foreach ($request->file('laporan_perjalanan') as $file) {
            $filename = uniqid('doc_') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/documents', $filename);

            Document::create([
                'travel_request_id' => $travelRequest->id,
                'uploaded_by' => Auth::id(),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'document_type' => 'report',
                'description' => 'Laporan perjalanan dinas',
                'is_required' => false,
                'is_verified' => false,
            ]);

            $uploadedCount++;
        }

        return redirect()->back()->with('success', "$uploadedCount laporan perjalanan dinas berhasil diunggah.");
    }
}