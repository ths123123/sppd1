<div class="bg-white rounded-xl shadow-lg p-8">
    <h2 class="text-xl font-bold mb-4 text-indigo-700 flex items-center gap-2"><i class="fas fa-info-circle"></i> Tentang Sistem</h2>
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
        <div>
            <dt class="text-sm font-medium text-gray-500">Nama Aplikasi</dt>
            <dd class="mt-1 text-sm text-gray-900">SPPD KPU Kabupaten Cirebon</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Versi Aplikasi</dt>
            <dd class="mt-1 text-sm text-gray-900">1.0.0</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Framework</dt>
            <dd class="mt-1 text-sm text-gray-900">Laravel {{ app()->version() }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">PHP Version</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ phpversion() }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Database</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ config('database.connections.' . config('database.default') . '.driver') }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500">Kontak Admin</dt>
            <dd class="mt-1 text-sm text-gray-900">admin@kpu-cirebon.go.id</dd>
        </div>
    </dl>
    <div class="mt-8 text-center text-gray-500 text-sm">
        &copy; {{ date('Y') }} KPU Kabupaten Cirebon. All rights reserved.
    </div>
</div> 