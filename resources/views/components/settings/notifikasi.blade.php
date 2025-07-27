<div class="bg-white rounded-xl shadow-lg p-8">
    <h2 class="text-xl font-bold mb-4 text-indigo-700 flex items-center gap-2"><i class="fas fa-bell"></i> Pengaturan Notifikasi</h2>
    <form action="{{ route('settings.save') }}" method="POST">
        @csrf
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notifikasi Sistem</label>
            <div class="flex items-center mt-2">
                <input type="checkbox" name="notification_enabled" id="notification_enabled" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ ($settings['notification_enabled'] ?? true) ? 'checked' : '' }}>
                <label for="notification_enabled" class="ml-2 block text-sm text-gray-700">Aktifkan notifikasi sistem</label>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
    <hr class="my-8">
    <h3 class="text-lg font-semibold mb-2">Preferensi Notifikasi Pengguna</h3>
    <form action="{{ route('settings.user.save') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Preferensi Notifikasi</label>
                <select name="notification_preference" class="form-select rounded-md shadow-sm w-full">
                    <option value="all" {{ ($userSettings['notification_preference'] ?? 'all') == 'all' ? 'selected' : '' }}>Semua notifikasi</option>
                    <option value="important" {{ ($userSettings['notification_preference'] ?? 'all') == 'important' ? 'selected' : '' }}>Hanya penting</option>
                    <option value="none" {{ ($userSettings['notification_preference'] ?? 'all') == 'none' ? 'selected' : '' }}>Tidak ada</option>
                </select>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-save mr-2"></i> Simpan Preferensi
            </button>
        </div>
    </form>
</div> 