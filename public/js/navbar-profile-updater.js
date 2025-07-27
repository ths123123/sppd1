// Auto-update navbar profile photo when profile is updated
document.addEventListener('DOMContentLoaded', function() {
    // Create navbar profile updater
    window.navbarProfileUpdater = {
        // Update navbar profile photo
        updateNavbarPhoto: function(avatarUrl, userName) {
            console.log('Updating navbar photo:', avatarUrl, userName);
            
            // Add cache busting parameter to prevent browser caching
            if (avatarUrl && !avatarUrl.includes('?')) {
                avatarUrl = avatarUrl + '?v=' + new Date().getTime();
            }
            
            // Update small navbar photo
            const navbarPhoto = document.getElementById('navbar-profile-photo');
            if (navbarPhoto) {
                if (avatarUrl) {
                    // If there's a new avatar, update the image
                    if (navbarPhoto.tagName === 'IMG') {
                        navbarPhoto.src = avatarUrl;
                        navbarPhoto.className = 'min-w-[40px] min-h-[40px] max-w-[40px] max-h-[40px] w-full h-full object-cover rounded-full shadow-md aspect-square';
                    } else {
                        // Replace div with img element
                        const imgElement = document.createElement('img');
                        imgElement.id = 'navbar-profile-photo';
                        imgElement.src = avatarUrl;
                        imgElement.alt = userName || 'Profile';
                        imgElement.className = 'min-w-[40px] min-h-[40px] max-w-[40px] max-h-[40px] w-full h-full object-cover rounded-full shadow-md aspect-square';
                        navbarPhoto.parentNode.replaceChild(imgElement, navbarPhoto);
                    }
                } else if (userName) {
                    // If no avatar, show initials
                    if (navbarPhoto.tagName === 'IMG') {
                        const divElement = document.createElement('div');
                        divElement.id = 'navbar-profile-photo';
                        divElement.className = 'min-w-[40px] min-h-[40px] max-w-[40px] max-h-[40px] w-full h-full rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center border-2 border-slate-600 shadow-md';
                        divElement.innerHTML = `<span class="text-white text-sm font-semibold">${userName.charAt(0)}</span>`;
                        navbarPhoto.parentNode.replaceChild(divElement, navbarPhoto);
                    }
                }
            }

            // Update dropdown large profile photo
            const dropdownPhotos = document.querySelectorAll('.profile-dropdown img, .w-16.h-16 img');
            dropdownPhotos.forEach(photo => {
                if (photo.tagName === 'IMG' && avatarUrl) {
                    photo.src = avatarUrl;
                }
            });

            // Update any other profile photos on the page
            const allProfilePhotos = document.querySelectorAll('img[src*="avatar"], img[alt*="profile"], img[alt*="Profile"]');
            allProfilePhotos.forEach(photo => {
                if (avatarUrl && photo.id !== 'navbar-profile-photo' && !photo.src.includes(avatarUrl.split('?')[0])) {
                    photo.src = avatarUrl;
                }
            });
        },

        // Handle profile update response
        handleProfileUpdateResponse: function(data) {
            if (data.success) {
                let avatarUrl = null;
                
                // Check different possible avatar URL formats in the response
                if (data.avatar_url) {
                    avatarUrl = data.avatar_url;
                } else if (data.user && data.user.avatar) {
                    avatarUrl = data.user.avatar.startsWith('storage/') 
                        ? '/storage/' + data.user.avatar.replace('storage/', '')
                        : '/storage/' + data.user.avatar;
                }
                
                if (avatarUrl) {
                    this.updateNavbarPhoto(avatarUrl, data.user ? data.user.name : null);
                    // Show success notification
                    this.showNotification('Profile updated successfully!', 'success');
                }
            }
        },

        // Show notification
        showNotification: function(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}-circle mr-2"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        },

        // Update navbar name
        updateNavbarName: function(name) {
            const nameElements = document.querySelectorAll('.profile-dropdown .text-black.font-semibold');
            nameElements.forEach(el => {
                el.textContent = name;
            });
        },
        
        // Update navbar email
        updateNavbarEmail: function(email) {
            const emailElements = document.querySelectorAll('.profile-dropdown .text-gray-500.text-xs');
            emailElements.forEach(el => {
                el.textContent = email;
            });
        },

        // Refresh navbar from current profile data
        refreshNavbarPhoto: function() {
            const profileImg = document.querySelector('#profile-photo-preview, .profile-photo-preview');
            if (profileImg && profileImg.src && !profileImg.src.includes('placeholder')) {
                const userName = document.querySelector('meta[name="user-name"]')?.content || 'User';
                this.updateNavbarPhoto(profileImg.src, userName);
                this.showNotification('Profile photo updated in navbar!', 'success');
            }
        }
    };

    // Listen for profile update events
    document.addEventListener('profileUpdated', function(event) {
        const { avatar, user } = event.detail;
        window.navbarProfileUpdater.updateNavbarPhoto(avatar, user.name);
    });
    
    // Hapus kode yang menyebabkan error
    // setTimeout(() => {
    //     window.navbarProfileUpdater.refreshNavbarPhoto();
    // }, 1000);
});
