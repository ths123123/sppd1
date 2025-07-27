@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data="profileManager()">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-lg font-bold text-gray-900 mb-2">
                <i class="fas fa-user-circle mr-2 text-indigo-600 text-base"></i>
                Profile Saya
            </h1>
            <p class="text-base text-gray-600">Kelola informasi profile dan pengaturan akun Anda</p>
        </div>

        <!-- Success Messages -->
        @if(session('status') === 'profile-updated')
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i>Profile berhasil diperbarui!
            </div>
        @endif

        @if(session('status') === 'profile-extended-updated')
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i>Informasi tambahan berhasil diperbarui!
            </div>
        @endif

        <!-- Profile Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-8">
            <!-- Profile Header -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-8">
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        <img src="{{ $user->avatar_url }}"
                             alt="{{ $user->name }}"
                             class="w-24 h-24 rounded-full border-4 border-white shadow-lg object-cover">
                        <button @click="openAvatarModal()"
                                class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-camera text-gray-600 text-sm"></i>
                        </button>
                    </div>
                    <div class="text-white">
                        <h2 class="text-base font-bold">{{ $user->name }}</h2>
                        @if($user->role === 'ppk')
                            <p class="text-indigo-100 text-xs">Role: Pejabat Pembuat Komitmen</p>
                        @else
                            <p class="text-indigo-100 text-xs">Role: {{ $user->getRoleDisplayName() }}</p>
                        @endif
                        <p class="text-indigo-200 text-xs">{{ $user->email }}</p>
                        @if($user->phone)
                        <p class="text-indigo-200 text-xs">
                            <i class="fas fa-phone mr-1 text-xs"></i>{{ $user->phone }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6">
                    <button @click="activeTab = 'basic'"
                            :class="activeTab === 'basic' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-2 px-1 border-b-2 font-medium text-xs transition-colors">
                        <i class="fas fa-user mr-2 text-xs"></i>Informasi Dasar
                    </button>
                    <button @click="activeTab = 'extended'"
                            :class="activeTab === 'extended' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-2 px-1 border-b-2 font-medium text-xs transition-colors">
                        <i class="fas fa-id-card mr-2 text-xs"></i>Detail Profile
                    </button>
                    <button @click="activeTab = 'security'"
                            :class="activeTab === 'security' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="py-2 px-1 border-b-2 font-medium text-xs transition-colors">
                        <i class="fas fa-shield-alt mr-2 text-xs"></i>Keamanan
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Basic Information Tab -->
                <div x-show="activeTab === 'basic'" class="space-y-6">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-1 text-gray-400"></i>Nama Lengkap
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-1 text-gray-400"></i>Email
                                </label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-phone mr-1 text-gray-400"></i>Nomor HP
                                </label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                       placeholder="08123456789"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-building mr-1 text-gray-400"></i>Bagian/Departemen
                                </label>
                                <input type="text" name="department" id="department" value="{{ old('department', $user->department) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('department')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-quote-left mr-1 text-gray-400"></i>Bio Singkat
                                </label>
                                <textarea name="bio" id="bio" rows="3"
                                          placeholder="Ceritakan sedikit tentang diri Anda..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Extended Information Tab -->
                <div x-show="activeTab === 'extended'" class="space-y-6">
                    <form method="POST" action="{{ route('profile.update.extended') }}">
                        @csrf
                        @method('patch')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-id-badge mr-1 text-gray-400"></i>ID Staff
                                </label>
                                <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $user->employee_id) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('employee_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-birthday-cake mr-1 text-gray-400"></i>Tanggal Lahir
                                </label>
                                <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @error('birth_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-venus-mars mr-1 text-gray-400"></i>Jenis Kelamin
                                </label>
                                <select name="gender" id="gender"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Pilih...</option>
                                    <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>Alamat
                                </label>
                                <textarea name="address" id="address" rows="3"
                                          placeholder="Alamat lengkap..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-6">
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                                <i class="fas fa-save mr-2"></i>Simpan Detail
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Tab -->
                <div x-show="activeTab === 'security'" class="space-y-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Avatar Upload Modal -->
    <div x-show="showAvatarModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         style="display: none;">

        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-base font-medium text-gray-900 mb-4">
                    <i class="fas fa-camera mr-2 text-indigo-600 text-xs"></i>
                    Upload Foto Profile
                </h3>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('patch')

                    <div class="mb-4">
                        <input type="file" name="avatar" id="avatar-upload" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">Max: 2MB. Format: JPG, PNG, GIF</p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeAvatarModal()"
                                class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="w-full text-center mt-8 mb-4">
        <p class="text-sm text-gray-500">Terakhir diperbarui: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>

<script>
function profileManager() {
    return {
        activeTab: 'basic',
        showAvatarModal: false,

        openAvatarModal() {
            this.showAvatarModal = true;
        },

        closeAvatarModal() {
            this.showAvatarModal = false;
        }
    }
}
</script>
@endsection
