<form id="password-form" method="POST" action="{{ route('password.update') }}" class="space-y-6">
    @csrf
    @method('put')
    <div>
        <label for="current_password" class="block text-sm font-medium text-gray-900">Password Lama</label>
        <input id="current_password" name="current_password" type="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
        @if(isset($errors) && $errors->has('current_password', 'updatePassword'))
            <span class="text-sm text-red-600">{{ $errors->first('current_password', 'updatePassword') }}</span>
        @endif
    </div>
    <div>
        <label for="password" class="block text-sm font-medium text-gray-900">Password Baru</label>
        <input id="password" name="password" type="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
        @if(isset($errors) && $errors->has('password', 'updatePassword'))
            <span class="text-sm text-red-600">{{ $errors->first('password', 'updatePassword') }}</span>
        @endif
    </div>
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-900">Konfirmasi Password Baru</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
        @if(isset($errors) && $errors->has('password_confirmation', 'updatePassword'))
            <span class="text-sm text-red-600">{{ $errors->first('password_confirmation', 'updatePassword') }}</span>
        @endif
    </div>
    <div>
        <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-md transition">Update Password</button>
    </div>
    @if (session('status') === 'password-updated')
        <div id="password-status" class="text-green-600 text-sm mt-2">Password berhasil diperbarui!</div>
    @endif
</form>