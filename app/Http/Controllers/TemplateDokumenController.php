<?php

namespace App\Http\Controllers;

use App\Models\TemplateDokumen;
use App\Services\TemplateDokumenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TemplateDokumenController extends Controller
{
    protected $service;

    public function __construct(TemplateDokumenService $service)
    {
        $this->service = $service;
    }

    // List semua template
    public function index()
    {
        $templates = TemplateDokumen::with(['creator', 'updater'])->orderByDesc('created_at')->get();
        return view('templates.manage', compact('templates'));
    }

    // Upload template baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_template' => 'required|string|max:255',
            'jenis_template' => 'required|in:spd,sppd,laporan_akhir',
            'file' => 'required|file|mimes:docx,pdf|max:5120',
            'status_aktif' => 'nullable|boolean',
        ]);
        $template = $this->service->uploadTemplate($data, $request->file('file'));
        return redirect()->route('templates.index')->with('success', 'Template berhasil diupload.');
    }

    // Preview template (PDF inline, DOCX download/info)
    public function preview(TemplateDokumen $template)
    {
        if ($template->tipe_file === 'pdf') {
            $path = Storage::disk('public')->path($template->path_file);
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $template->nama_template . '.pdf"',
            ]);
        } elseif ($template->tipe_file === 'docx') {
            // Untuk DOCX, tampilkan info file dan tombol download
            return view('templates.preview_docx', compact('template'));
        }
        abort(404);
    }

    // Aktifkan template
    public function activate(TemplateDokumen $template)
    {
        $this->service->activateTemplate($template);
        return redirect()->route('templates.index')->with('success', 'Template diaktifkan.');
    }

    // Hapus template
    public function destroy(TemplateDokumen $template)
    {
        try {
            $this->service->deleteTemplate($template);
            return redirect()->route('templates.index')->with('success', 'Template dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('templates.index')->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }

    // Edit/update template
    public function update(Request $request, TemplateDokumen $template)
    {
        $data = $request->validate([
            'nama_template' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:docx,pdf|max:5120',
            'status_aktif' => 'nullable|boolean',
        ]);
        $file = $request->file('file');
        $this->service->updateTemplate($template, $data, $file);
        return redirect()->route('templates.index')->with('success', 'Template diupdate.');
    }
} 