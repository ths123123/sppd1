<?php $__env->startSection('content'); ?>
<div class="py-6 min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Edit Profile</h2>
            <p class="text-gray-600">Kelola informasi profil dan keamanan akun Anda</p>
            <div class="mt-4">
                <a href="<?php echo e(route('profile.show')); ?>"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Profile
                </a>
            </div>
        </div>

        <div class="space-y-8">
            <!-- Profile Information Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-user-edit mr-3"></i>
                        Profile Information
                    </h3>
                    <p class="text-blue-100 mt-1">Update your personal and professional information</p>
                </div>
                <div class="p-8">
                    <?php echo $__env->make('profile.partials.update-profile-information-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <!-- Password Update Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-red-600 to-pink-600 px-8 py-6">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-lock mr-3"></i>
                        Update Password
                    </h3>
                    <p class="text-red-100 mt-1">Ensure your account uses a strong password to stay secure</p>
                </div>
                <div class="p-8">
                    <?php echo $__env->make('profile.partials.update-password-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\pkl\SPPD-KP1\SPPD-KPUKP1\resources\views/profile/edit.blade.php ENDPATH**/ ?>