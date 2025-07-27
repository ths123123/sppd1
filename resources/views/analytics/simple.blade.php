@extends('layouts.app')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="mb-8">
        <h1 class="text-lg font-bold text-gray-900">Dashboard Analitik SPPD</h1>
        <p class="text-gray-600 mt-2">Analisis data perjalanan dinas KPU Kabupaten Cirebon</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-base font-semibold mb-4">Status Sistem</h2>
        <div class="text-green-600">
            ✅ Analytics controller berhasil di-load!
        </div>
        <div class="mt-4">
            <p><strong>User:</strong> {{ $user->name }}</p>
            <p><strong>Role:</strong> {{ $user->role }}</p>
            <p><strong>Akses Analytics:</strong> Diizinkan</p>
        </div>
    </div>

    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-base text-blue-800">Status Implementasi</h3>
        <p class="text-blue-700 mt-2">Controller analytics sederhana berhasil dijalankan. Fitur analytics lengkap akan segera ditambahkan.</p>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endsection
