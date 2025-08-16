<form id="password-form" method="POST" action="<?php echo e(route('password.update')); ?>" class="space-y-6">
    <?php echo csrf_field(); ?>
    <?php echo method_field('put'); ?>
    <div>
        <label for="current_password" class="block text-sm font-medium text-gray-900">Password Lama</label>
        <input id="current_password" name="current_password" type="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
        <?php if(isset($errors) && $errors->has('current_password', 'updatePassword')): ?>
            <span class="text-sm text-red-600"><?php echo e($errors->first('current_password', 'updatePassword')); ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="password" class="block text-sm font-medium text-gray-900">Password Baru</label>
        <input id="password" name="password" type="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
        <?php if(isset($errors) && $errors->has('password', 'updatePassword')): ?>
            <span class="text-sm text-red-600"><?php echo e($errors->first('password', 'updatePassword')); ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-900">Konfirmasi Password Baru</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
        <?php if(isset($errors) && $errors->has('password_confirmation', 'updatePassword')): ?>
            <span class="text-sm text-red-600"><?php echo e($errors->first('password_confirmation', 'updatePassword')); ?></span>
        <?php endif; ?>
    </div>
    <div>
        <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold rounded-md transition">Update Password</button>
    </div>
    <?php if(session('status') === 'password-updated'): ?>
        <div id="password-status" class="text-green-600 text-sm mt-2">Password berhasil diperbarui!</div>
    <?php endif; ?>
</form><?php /**PATH D:\pkl\SPPD-KP1\SPPD-KPUKP1\resources\views/profile/partials/update-password-form.blade.php ENDPATH**/ ?>