<section>
    <form id="profile-form" method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')
        <input type="hidden" name="view_profile" value="1">
        <!-- Profile Photo Section -->
        <div class="flex flex-col items-center space-y-4">
            <div class="relative">
                <!-- Current Avatar with Creative Border -->
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 p-1 shadow-xl">
                    <div class="w-full h-full rounded-full bg-white p-1">
                        <div class="w-full h-full rounded-full overflow-hidden">
                            @if($user->avatar)
                                <img id="profile-photo-preview"
                                     src="{{ asset('storage/' . $user->avatar) }}"
                                     alt="Profile Photo"
                                     class="w-full h-full object-cover object-center">
                            @else
                                <div id="profile-placeholder" class="w-full h-full rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upload Button Overlay -->
                <label for="avatar" class="absolute bottom-0 right-0 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-3 cursor-pointer transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </label>
            </div>

            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/jpg,image/gif" class="hidden" onchange="previewImage(this)">
            <p class="text-xs text-gray-900">Click the camera icon to upload a new photo (Max: 2MB)</p>
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <!-- Basic Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="name" :value="__('Nama Lengkap')" class="text-gray-900" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-white text-gray-900" :value="old('name', $user->name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>
            <div>
                <x-input-label for="email" :value="__('Email')" class="text-gray-900" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-white text-gray-900" :value="old('email', $user->email)" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label for="nip" :value="__('NIP')" class="text-gray-900" />
                <x-text-input id="nip" name="nip" type="text" maxlength="18" pattern="[0-9]*" inputmode="numeric" class="mt-1 block w-full bg-white text-gray-900" :value="old('nip', $user->nip)" placeholder="198402132009121001" />
                <x-input-error class="mt-2" :messages="$errors->get('nip')" />
            </div>
            <div>
                <x-input-label for="jabatan" :value="__('Jabatan')" class="text-gray-900" />
                <x-text-input id="jabatan" name="jabatan" type="text" class="mt-1 block w-full bg-white text-gray-900" :value="old('jabatan', $user->jabatan)" placeholder="Jabatan" />
                <x-input-error class="mt-2" :messages="$errors->get('jabatan')" />
            </div>
            <div>
                <x-input-label for="role" :value="__('Role')" />
                <x-text-input id="role" name="role" type="text" class="mt-1 block w-full bg-gray-50 text-gray-900" :value="$user->role === 'ppk' ? 'Pejabat Pembuat Komitmen' : $user->getRoleDisplayName()" readonly />
            </div>
            <div>
                <x-input-label for="phone" :value="__('Phone Number')" class="text-gray-900" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full bg-white text-gray-900" :value="old('phone', $user->phone)" placeholder="+62 812 3456 7890" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
            <div>
                <x-input-label for="address" :value="__('Address')" class="text-gray-900" />
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md bg-white text-gray-900" placeholder="Enter your full address">{{ old('address', $user->address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>
            <div>
                <x-input-label for="pangkat" :value="__('Pangkat')" class="text-gray-900" />
                <x-text-input id="pangkat" name="pangkat" type="text" class="mt-1 block w-full bg-white text-gray-900" :value="old('pangkat', $user->pangkat)" placeholder="Pangkat" />
                <x-input-error class="mt-2" :messages="$errors->get('pangkat')" />
            </div>
            <div>
                <x-input-label for="golongan" :value="__('Golongan')" class="text-gray-900" />
                <x-text-input id="golongan" name="golongan" type="text" class="mt-1 block w-full bg-white text-gray-900" :value="old('golongan', $user->golongan)" placeholder="Golongan" />
                <x-input-error class="mt-2" :messages="$errors->get('golongan')" />
            </div>
            <div>
                <x-input-label for="unit_kerja" :value="__('Unit Kerja')" class="text-gray-900" />
                <x-text-input id="unit_kerja" name="unit_kerja" type="text" class="mt-1 block w-full bg-white text-gray-900" :value="old('unit_kerja', $user->unit_kerja)" placeholder="Unit Kerja" />
                <x-input-error class="mt-2" :messages="$errors->get('unit_kerja')" />
            </div>
            <div>
                <x-input-label for="birth_date" :value="__('Birth Date')" class="text-gray-900" />
                <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full bg-white text-gray-900" :value="old('birth_date', $user->birth_date?->format('Y-m-d'))" />
                <x-input-error class="mt-2" :messages="$errors->get('birth_date')" />
            </div>
            <div>
                <x-input-label for="gender" :value="__('Gender')" class="text-gray-900" />
                <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 rounded-md bg-white text-gray-900">
                    <option value="">Select Gender</option>
                    <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Female</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('gender')" />
            </div>
        </div>

        <!-- Bio -->
        <div>
            <x-input-label for="bio" :value="__('Bio')" class="text-gray-900" />
            <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white text-gray-900" placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button type="submit" id="profile-save-btn" class="bg-blue-600 hover:bg-blue-700 text-white">
                <i class="fas fa-save mr-2"></i>
                {{ __('Update Profile') }}
            </x-primary-button>

            <div id="profile-status" class="hidden">
                <p class="text-sm text-green-600">Profile updated successfully!</p>
            </div>

            <div id="profile-error" class="hidden">
                <p class="text-sm text-red-600">Error updating profile. Please try again.</p>
            </div>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >{{ __('Profile updated successfully!') }}</p>
            @endif
        </div>
    </form>

    <!-- JavaScript for Image Preview -->
    <!-- Profile Photo Preview Modal -->
    <div id="profile-preview-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Preview Profile Photo</h3>
            <button type="button" id="close-preview-modal" class="text-gray-900 hover:text-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex flex-col items-center space-y-4">
                <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-indigo-100">
                    <img id="modal-preview-image" class="w-full h-full object-cover object-center" src="" alt="Profile Preview">
                </div>
                <p class="text-sm text-gray-900">This is how your profile photo will look.</p>
                <div class="flex space-x-3 mt-4">
                    <button type="button" id="cancel-photo" class="px-4 py-2 bg-gray-200 text-gray-900 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="button" id="confirm-photo" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Confirm Photo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Validate file size (2MB = 2048KB)
                if (file.size > 2048 * 1024) {
                    alert('File size must be less than 2MB');
                    input.value = '';
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, JPG, GIF)');
                    input.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    // Show the preview modal
                    const modal = document.getElementById('profile-preview-modal');
                    const modalImage = document.getElementById('modal-preview-image');
                    modalImage.src = e.target.result;
                    modal.classList.remove('hidden');

                    // Store the image data for later use
                    window.tempImageData = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('avatar');
            const previewModal = document.getElementById('profile-preview-modal');
            const closeModalBtn = document.getElementById('close-preview-modal');
            const cancelPhotoBtn = document.getElementById('cancel-photo');
            const confirmPhotoBtn = document.getElementById('confirm-photo');

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    previewImage(this);
                });
            }

            // Close modal handlers
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', function() {
                    previewModal.classList.add('hidden');
                    fileInput.value = ''; // Clear the file input
                });
            }

            if (cancelPhotoBtn) {
                cancelPhotoBtn.addEventListener('click', function() {
                    previewModal.classList.add('hidden');
                    fileInput.value = ''; // Clear the file input
                });
            }

            if (confirmPhotoBtn) {
                confirmPhotoBtn.addEventListener('click', function() {
                    // Apply the image to the profile preview
                    const profileContainer = document.querySelector('.w-32.h-32 .w-full.h-full.rounded-full.overflow-hidden');

                    // Remove placeholder if exists
                    const placeholder = document.getElementById('profile-placeholder');
                    if (placeholder) {
                        placeholder.remove();
                    }

                    // Update or create image element
                    let imgElement = document.getElementById('profile-photo-preview');
                    if (!imgElement) {
                        imgElement = document.createElement('img');
                        imgElement.id = 'profile-photo-preview';
                        imgElement.className = 'w-full h-full object-cover object-center';
                        profileContainer.appendChild(imgElement);
                    }

                    // Set the image source from the stored temp data
                    if (window.tempImageData) {
                        imgElement.src = window.tempImageData + '?v=' + new Date().getTime();
                        imgElement.alt = 'Profile Photo Preview';

                        // Add loading animation
                        imgElement.style.opacity = '0';
                        setTimeout(() => {
                            imgElement.style.transition = 'opacity 0.3s ease-in-out';
                            imgElement.style.opacity = '1';
                        }, 100);
                    }

                    // Close the modal
                    previewModal.classList.add('hidden');
                });
            }
        });

        // Add drag and drop functionality
        document.addEventListener('DOMContentLoaded', function() {
            const avatarContainer = document.querySelector('.w-32.h-32');
            const fileInput = document.getElementById('avatar');

            if (avatarContainer && fileInput) {
                // Prevent default drag behaviors
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    avatarContainer.addEventListener(eventName, preventDefaults, false);
                    document.body.addEventListener(eventName, preventDefaults, false);
                });

                // Highlight drop area when item is dragged over it
                ['dragenter', 'dragover'].forEach(eventName => {
                    avatarContainer.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    avatarContainer.addEventListener(eventName, unhighlight, false);
                });

                // Handle dropped files
                avatarContainer.addEventListener('drop', handleDrop, false);

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                function highlight(e) {
                    avatarContainer.classList.add('border-2', 'border-blue-400', 'border-dashed');
                }

                function unhighlight(e) {
                    avatarContainer.classList.remove('border-2', 'border-blue-400', 'border-dashed');
                }

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;

                    if (files.length > 0) {
                        fileInput.files = files;
                        previewImage(fileInput);
                    }
                }
            }
        });

        // AJAX form submission
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';

            // Show success message
            function showSuccessMessage(message) {
                const statusElement = document.getElementById('profile-status');
                statusElement.textContent = message;
                statusElement.classList.remove('hidden');
                setTimeout(() => {
                    statusElement.classList.add('opacity-0');
                    setTimeout(() => {
                        statusElement.classList.add('hidden');
                        statusElement.classList.remove('opacity-0');
                    }, 500);
                }, 3000);
            }

            // Show error message
            function showErrorMessage(message) {
                const statusElement = document.getElementById('profile-status');
                statusElement.textContent = message;
                statusElement.classList.remove('hidden', 'text-green-600');
                statusElement.classList.add('text-red-600');
                setTimeout(() => {
                    statusElement.classList.add('opacity-0');
                    setTimeout(() => {
                        statusElement.classList.add('hidden');
                        statusElement.classList.remove('opacity-0', 'text-red-600');
                        statusElement.classList.add('text-green-600');
                    }, 500);
                }, 3000);
            }

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update navbar profile immediately
                        if (window.navbarProfileUpdater && data.user) {
                            if (data.avatar_url) {
                                window.navbarProfileUpdater.updateNavbarPhoto(data.avatar_url, data.user.name);

                                // Force refresh all profile photos on the page
                                const allProfilePhotos = document.querySelectorAll('img[src*="avatar"], img[alt*="profile"], img[alt*="Profile"]');
                                allProfilePhotos.forEach(photo => {
                                    if (data.avatar_url && photo.src !== data.avatar_url) {
                                        const newSrc = data.avatar_url + (data.avatar_url.includes('?') ? '&' : '?') + 'v=' + new Date().getTime();
                                        photo.src = newSrc;
                                    }
                                });
                            }

                            if (data.user.name) {
                                window.navbarProfileUpdater.updateNavbarName(data.user.name);
                            }

                            if (data.user.email) {
                                window.navbarProfileUpdater.updateNavbarEmail(data.user.email);
                            }
                        }

                        // Show success message
                        showSuccessMessage('Profile updated successfully!');

                        // Reset button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Changes';

                        // Redirect to profile page after successful update
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }

                    } else {
                        // Handle error
                        showErrorMessage(data.message || 'Failed to update profile');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Changes';
                    }
                })
                .catch(error => {
                    console.error('Error updating profile:', error);
                    showErrorMessage('Network error occurred');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Changes';
                });
        });

        // Success message function
        function showSuccessMessage(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-check-circle"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Error message function
        function showErrorMessage(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300';
            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);

            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    </script>
</section>
