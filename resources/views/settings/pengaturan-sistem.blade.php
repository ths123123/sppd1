@extends('layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 text-center bg-transparent" style="padding-top: 6rem;">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 max-w-md w-full">
        <i class="fas fa-cog text-5xl text-gray-300 mb-6"></i>
        <h2 class="text-2xl font-bold text-gray-700 mb-2">Halaman Pengaturan Dinonaktifkan</h2>
        <p class="text-gray-500 text-lg mb-0">Menu pengaturan sistem saat ini tidak tersedia.<br>Silakan hubungi admin jika membutuhkan perubahan konfigurasi.</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush
