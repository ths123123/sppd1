@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-12">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Enhanced Header Section -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl shadow-lg mb-4">
                <i class="fas fa-file-word text-white text-base"></i>
            </div>
            <h1 class="text-lg font-bold text-gray-900 mb-2 tracking-tight">
                Manajemen Template Dokumen
            </h1>
            <p class="text-base text-gray-600 max-w-xl mx-auto leading-relaxed">
                Kelola template dokumen resmi KPU dengan sistem yang terintegrasi dan aman
            </p>
        </div>

        <!-- Upload Form Section -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <h2 class="text-base font-semibold text-white flex items-center">
                    <i class="fas fa-upload mr-2 text-base"></i>
                    Upload Template Baru
                </h2>
                <p class="text-blue-100 mt-1 text-xs">Tambahkan template dokumen dengan format yang sesuai</p>
            </div>
            
            <div class="p-8">
                <form action="{{ route('templates.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                        <!-- Nama Template -->
                        <div class="lg:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-tag mr-2 text-blue-600"></i>
                                Nama Template
                            </label>
                            <input type="text" name="nama_template" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white" 
                                   placeholder="Masukkan nama template"
                                   required>
                        </div>

                        <!-- Jenis Template -->
                        <div class="lg:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-list-alt mr-2 text-blue-600"></i>
                                Jenis Template
                            </label>
                            <select name="jenis_template" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white" 
                                    required>
                                <option value="">-- Pilih Jenis Dokumen --</option>
                                <option value="spd">SPD</option>
                                <option value="sppd">SPPD</option>
                                <option value="laporan_akhir">Laporan Akhir</option>
                            </select>
                        </div>

                        <!-- File Upload -->
                        <div class="lg:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-file-upload mr-2 text-blue-600"></i>
                                File Template
                            </label>
                            <input type="file" name="file" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50 focus:bg-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" 
                                   accept=".docx,.pdf" required>
                            <p class="text-xs text-gray-500 mt-2">Format: DOCX/PDF, Maksimal 5MB</p>
                        </div>

                        <!-- Actions -->
                        <div class="lg:col-span-1 flex flex-col justify-between">
                            <div class="flex items-center mb-4">
                                <input type="checkbox" name="status_aktif" value="1" id="aktif" 
                                       class="w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                <label for="aktif" class="ml-3 text-sm font-medium text-gray-700">
                                    Jadikan template aktif
                                </label>
                            </div>
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-105 shadow-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Upload Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Templates List Section -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                <h2 class="text-base font-semibold text-white flex items-center">
                    <i class="fas fa-table mr-2 text-base"></i>
                    Daftar Template Dokumen
                </h2>
                <p class="text-gray-300 mt-1 text-xs">Kelola dan pantau semua template yang tersedia</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-file-alt mr-2"></i>Nama Template
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-tag mr-2"></i>Jenis
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-file-code mr-2"></i>Tipe File
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-toggle-on mr-2"></i>Status
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <i class="fas fa-cogs mr-2"></i>Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($templates as $template)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-file-word text-blue-600 text-base"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $template->nama_template }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 uppercase">
                                    {{ strtoupper($template->jenis_template) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 uppercase">
                                    {{ $template->tipe_file }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($template->status_aktif)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-check-circle mr-1"></i>Aktif
                                    </span>
                                @else
                                    <form action="{{ route('templates.activate', $template) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors duration-150">
                                            <i class="fas fa-play mr-1"></i>Aktifkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <a href="{{ route('templates.preview', $template) }}" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-indigo-600 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                                    <i class="fas fa-eye mr-2"></i>Preview
                                </a>
                                <form action="{{ route('templates.destroy', $template) }}" method="POST" class="inline" onsubmit="return confirm('Hapus template ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-red-600 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-150">
                                        <i class="fas fa-trash mr-2"></i>Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>
@endsection