{{-- Dashboard Header Component --}}
<div class="mb-8 bg-white p-6 rounded-lg shadow">
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard</h1>
        <p class="text-gray-600">Kelola dan pantau perjalanan dinas dengan sistem yang terintegrasi</p>
        <div class="mt-3 text-xs text-gray-500 bg-blue-50 rounded-lg px-4 py-2 border shadow-sm inline-block">
            Terakhir diperbarui: {{ $lastUpdated ?? now('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
        </div>
    </div>
</div>
