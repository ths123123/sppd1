@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto py-8">
    <h1 class="text-lg font-bold mb-4">Preview Template DOCX</h1>
    <div class="bg-white rounded shadow p-4 mb-4">
        <div class="mb-2"><span class="font-semibold">Nama:</span> {{ $template->nama_template }}</div>
        <div class="mb-2"><span class="font-semibold">Tipe:</span> {{ strtoupper($template->tipe_file) }}</div>
        <div class="mb-2"><span class="font-semibold">Ukuran:</span> {{ Storage::disk('public')->size($template->path_file) / 1024 | number_format(2) }} KB</div>
        <a href="{{ Storage::disk('public')->url($template->path_file) }}" class="btn btn-primary" download>Download File</a>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <a href="{{ route('templates.index') }}" class="text-blue-600 underline">&larr; Kembali ke daftar template</a>
</div>
@endsection 