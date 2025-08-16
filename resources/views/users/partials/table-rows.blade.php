@forelse($users as $user)
<tr class="hover:bg-gray-50 transition-colors duration-150">
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        <div class="flex items-center">
            <div class="relative">
                <div class="h-10 w-10 rounded-xl overflow-hidden shadow-sm">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                </div>
                @if($user->is_active)
                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></div>
                @endif
            </div>
            <div class="ml-4">
                <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                <div class="text-xs text-gray-500 flex items-center mt-0.5">
                    <i class="fas fa-envelope mr-1 text-gray-400"></i>
                    {{ $user->email }}
                </div>
            </div>
        </div>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900 font-medium">{{ $user->nip ?? '-' }}</div>
        <div class="text-xs text-gray-500 mt-0.5">{{ $user->jabatan ?? '-' }}</div>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        @php
            $roleStyles = [
                'staff' => 'bg-gray-100 text-gray-700 border-gray-200',
                'kasubbag' => 'bg-purple-100 text-purple-700 border-purple-200',
                'sekretaris' => 'bg-blue-100 text-blue-700 border-blue-200',
                'ppk' => 'bg-green-100 text-green-700 border-green-200',
                'admin' => 'bg-yellow-100 text-yellow-700 border-yellow-200'
            ];
            $style = $roleStyles[$user->role] ?? 'bg-gray-100 text-gray-700 border-gray-200';
        @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium {{ $style }} border">
            {{ $user->role === 'ppk' ? 'Pejabat Pembuat Komitmen' : $user->role }}
        </span>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">{{ $user->unit_kerja ?? '-' }}</div>
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        @if($user->is_active)
            <div class="flex items-center">
                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                <span class="text-sm font-medium text-green-700">Active</span>
            </div>
        @else
            <div class="flex items-center">
                <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                <span class="text-sm font-medium text-gray-500">Inactive</span>
            </div>
        @endif
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        @if($user->last_login_at)
            <div class="text-sm text-gray-900">
                {{ \Carbon\Carbon::parse($user->last_login_at)->format('d M Y') }}
            </div>
            <div class="text-xs text-gray-500">
                {{ \Carbon\Carbon::parse($user->last_login_at)->format('H:i') }}
            </div>
        @else
            <span class="text-sm text-gray-400">Never logged in</span>
        @endif
    </td>
    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
        <div class="flex items-center space-x-2">
            <button onclick="toggleUserStatus({{ $user->id }}, '{{ $user->name }}')"
                    class="px-3 py-1.5 {{ $user->is_active ? 'bg-red-100 text-red-600 hover:bg-red-200 hover:text-red-700' : 'bg-green-100 text-green-600 hover:bg-green-200 hover:text-green-700' }} rounded-lg transition-all duration-200 flex items-center justify-center text-sm font-medium"
                    title="{{ $user->is_active ? 'Nonaktif' : 'Aktif' }}">
                {{ $user->is_active ? 'Nonaktif' : 'Aktif' }} ↔
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="7" class="px-6 py-16 text-center">
        <div class="flex flex-col items-center">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-users text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 mb-1">No Users Found</h3>
            <p class="text-gray-500 text-sm">Start by adding your first user to the system.</p>
        </div>
    </td>
</tr>
@endforelse