<?php

namespace App\Services;

use App\Models\TemplateDokumen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class TemplateDokumenService
{
    /**
     * Upload dan simpan template dokumen baru.
     */
    public function uploadTemplate(array $data, UploadedFile $file): TemplateDokumen
    {
        $this->validateFile($file);
        $filename = 'template_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('templates', $filename, 'public');

        DB::beginTransaction();
        try {
            if (Arr::get($data, 'status_aktif', false)) {
                // Nonaktifkan template lain jika ingin aktif
                TemplateDokumen::where('status_aktif', true)->update(['status_aktif' => false]);
            }
            $template = TemplateDokumen::create([
                'nama_template' => $data['nama_template'],
                'jenis_template' => $data['jenis_template'],
                'path_file' => $path,
                'tipe_file' => $file->getClientOriginalExtension(),
                'status_aktif' => Arr::get($data, 'status_aktif', false),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            Log::info('Template dokumen diupload', ['template_id' => $template->id, 'user_id' => Auth::id()]);
            DB::commit();
            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validasi file template (DOCX/PDF, size max 5MB).
     */
    public function validateFile(UploadedFile $file): void
    {
        $allowed = ['docx', 'pdf'];
        if (!in_array($file->getClientOriginalExtension(), $allowed)) {
            throw ValidationException::withMessages(['file' => 'Format file harus DOCX atau PDF.']);
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw ValidationException::withMessages(['file' => 'Ukuran file maksimal 5MB.']);
        }
    }

    /**
     * Aktifkan template tertentu (nonaktifkan yang lain).
     */
    public function activateTemplate(TemplateDokumen $template): void
    {
        DB::transaction(function () use ($template) {
            TemplateDokumen::where('status_aktif', true)->update(['status_aktif' => false]);
            $template->update(['status_aktif' => true, 'updated_by' => Auth::id()]);
            Log::info('Template dokumen diaktifkan', ['template_id' => $template->id, 'user_id' => Auth::id()]);
        });
    }

    /**
     * Hapus template dokumen.
     */
    public function deleteTemplate(TemplateDokumen $template): void
    {
        Storage::disk('public')->delete($template->path_file);
        $template->delete();
        Log::info('Template dokumen dihapus', ['template_id' => $template->id, 'user_id' => Auth::id()]);
    }

    /**
     * Update nama template atau file.
     */
    public function updateTemplate(TemplateDokumen $template, array $data, UploadedFile $file = null): TemplateDokumen
    {
        DB::beginTransaction();
        try {
            if ($file) {
                $this->validateFile($file);
                Storage::disk('public')->delete($template->path_file);
                $filename = 'template_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('templates', $filename, 'public');
                $template->path_file = $path;
                $template->tipe_file = $file->getClientOriginalExtension();
            }
            $template->nama_template = $data['nama_template'] ?? $template->nama_template;
            $template->status_aktif = Arr::get($data, 'status_aktif', $template->status_aktif);
            $template->updated_by = Auth::id();
            $template->save();
            Log::info('Template dokumen diupdate', ['template_id' => $template->id, 'user_id' => Auth::id()]);
            DB::commit();
            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 