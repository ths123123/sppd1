<div class="bg-white rounded-xl shadow-lg p-8">
    <h2 class="text-xl font-bold mb-4 text-indigo-700 flex items-center gap-2"><i class="fas fa-shield-alt"></i> Keamanan Akun</h2>
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-2">Ganti Password</h3>
        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                    <input type="password" name="current_password" class="form-input rounded-md shadow-sm w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password" class="form-input rounded-md shadow-sm w-full" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-input rounded-md shadow-sm w-full" required>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-key mr-2"></i> Ganti Password
                </button>
            </div>
        </form>
    </div>
    <hr class="my-8">
    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-2">Two-Factor Authentication</h3>
        <p class="text-gray-600 mb-2">Fitur ini akan segera tersedia untuk meningkatkan keamanan akun Anda.</p>
        <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md cursor-not-allowed" disabled>Aktifkan 2FA (Coming Soon)</button>
    </div>
    <hr class="my-8">
    <div>
        <h3 class="text-lg font-semibold mb-2">Sesi Login & Info Keamanan</h3>
        <p class="text-gray-600">Untuk keamanan, selalu gunakan password yang kuat dan jangan bagikan akun Anda ke orang lain. Semua aktivitas login dicatat untuk audit trail.</p>
    </div>
</div> 