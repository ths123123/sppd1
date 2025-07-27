<div class="bg-white rounded-xl shadow-lg p-8">
    <h2 class="text-xl font-bold mb-4 text-indigo-700 flex items-center gap-2"><i class="fas fa-user"></i> Pengaturan Pengguna</h2>
    <form action="{{ route('settings.user.save') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode Tampilan</label>
                <select name="display_mode" class="form-select rounded-md shadow-sm w-full">
                    <option value="light" {{ ($userSettings['display_mode'] ?? 'light') == 'light' ? 'selected' : '' }}>Terang</option>
                    <option value="dark" {{ ($userSettings['display_mode'] ?? 'light') == 'dark' ? 'selected' : '' }}>Gelap</option>
                    <option value="system" {{ ($userSettings['display_mode'] ?? 'light') == 'system' ? 'selected' : '' }}>Ikuti Sistem</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pengguna</label>
                <input type="text" value="{{ $user->name }}" class="form-input rounded-md shadow-sm w-full bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" value="{{ $user->email }}" class="form-input rounded-md shadow-sm w-full bg-gray-100" readonly>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <input type="text" value="{{ ucfirst($user->role) }}" class="form-input rounded-md shadow-sm w-full bg-gray-100" readonly>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-save mr-2"></i> Simpan Preferensi
            </button>
        </div>
    </form>
</div> 