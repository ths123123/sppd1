/**
 * Navbar Profile Auto-Update System
 * Automatically updates profile photo in navbar when user changes their avatar
 */

class NavbarProfileUpdater {
    constructor() {
        this.init();
    }

    init() {
        // Listen for profile photo updates from the profile page
        this.listenForProfileUpdates();

        // Listen for storage events (cross-tab updates)
        this.listenForStorageEvents();

        // Set up mutation observer for dynamic updates
        this.setupMutationObserver();
    }

    /**
     * Listen for custom events when profile is updated
     */
    listenForProfileUpdates() {
        document.addEventListener('profileUpdated', (event) => {
            if (event.detail && event.detail.avatar) {
                this.updateNavbarPhoto(event.detail.avatar);
            }
        });

        document.addEventListener('profilePhotoChanged', (event) => {
            if (event.detail && event.detail.newPhotoUrl) {
                this.updateNavbarPhoto(event.detail.newPhotoUrl);
            }
        });
    }

    /**
     * Listen for localStorage changes (cross-tab communication)
     */
    listenForStorageEvents() {
        window.addEventListener('storage', (event) => {
            if (event.key === 'profilePhotoUpdated' && event.newValue) {
                const photoData = JSON.parse(event.newValue);
                this.updateNavbarPhoto(photoData.url);

                // Clear the flag
                localStorage.removeItem('profilePhotoUpdated');
            }
        });
    }

    /**
     * Set up mutation observer to watch for DOM changes
     */
    setupMutationObserver() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' &&
                    mutation.attributeName === 'src' &&
                    mutation.target.id === 'profile-photo-preview') {

                    // Profile photo preview changed, update navbar
                    const newSrc = mutation.target.src;
                    if (newSrc && newSrc.startsWith('blob:')) {
                        // It's a blob URL from file upload preview
                        this.updateNavbarPhoto(newSrc, true);
                    }
                }
            });
        });

        // Start observing
        const profilePhotoPreview = document.getElementById('profile-photo-preview');
        if (profilePhotoPreview) {
            observer.observe(profilePhotoPreview, {
                attributes: true,
                attributeFilter: ['src']
            });
        }
    }

    /**
     * Update the navbar profile photo
     * @param {string} newPhotoUrl - New photo URL
     * @param {boolean} isPreview - Whether this is a preview (blob URL)
     */
    updateNavbarPhoto(newPhotoUrl, isPreview = false) {
        const navbarPhoto = document.getElementById('navbar-profile-photo');
        const dropdownPhoto = document.getElementById('dropdown-profile-photo');

        if (!navbarPhoto) {
            console.warn('Navbar profile photo element not found');
            return;
        }

        // Update navbar photo
        if (navbarPhoto.tagName === 'IMG') {
            navbarPhoto.src = newPhotoUrl;
            navbarPhoto.style.opacity = '0.7';
            navbarPhoto.addEventListener('load', function() {
                this.style.opacity = '1';
                // Add success animation
                this.style.border = '3px solid #10b981';
                this.style.boxShadow = '0 0 20px rgba(16, 185, 129, 0.5)';
                setTimeout(() => {
                    this.style.border = '2px solid white';
                    this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                }, 2000);
            }, { once: true });
        } else {
            // If it's a div with initials, replace with img
            const container = navbarPhoto.parentElement;
            const newImg = document.createElement('img');

            newImg.id = 'navbar-profile-photo';
            newImg.src = newPhotoUrl;
            newImg.alt = 'Profile';
            newImg.className = 'min-w-[36px] min-h-[36px] max-w-[36px] max-h-[36px] w-full h-full object-cover rounded-full border-2 border-white shadow-md aspect-square';

            newImg.style.opacity = '0.7';
            newImg.addEventListener('load', function() {
                this.style.opacity = '1';
                // Add success animation
                this.style.border = '3px solid #10b981';
                this.style.boxShadow = '0 0 20px rgba(16, 185, 129, 0.5)';
                setTimeout(() => {
                    this.style.border = '2px solid white';
                    this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                }, 2000);
            }, { once: true });

            container.replaceChild(newImg, navbarPhoto);
        }

        // Update dropdown photo
        if (dropdownPhoto) {
            if (dropdownPhoto.tagName === 'IMG') {
                dropdownPhoto.src = newPhotoUrl;
                dropdownPhoto.style.opacity = '0.7';
                dropdownPhoto.addEventListener('load', function() {
                    this.style.opacity = '1';
                }, { once: true });
            } else {
                // If it's a div with initials, replace with img
                const container = dropdownPhoto.parentElement;
                const newImg = document.createElement('img');

                newImg.id = 'dropdown-profile-photo';
                newImg.src = newPhotoUrl;
                newImg.alt = 'Profile';
                newImg.className = 'w-full h-full object-cover rounded-full';

                newImg.style.opacity = '0.7';
                newImg.addEventListener('load', function() {
                    this.style.opacity = '1';
                }, { once: true });

                container.replaceChild(newImg, dropdownPhoto);
            }
        }

        // Store in localStorage for cross-tab updates
        if (!isPreview) {
            localStorage.setItem('profilePhotoUpdated', JSON.stringify({
                url: newPhotoUrl,
                timestamp: Date.now()
            }));
        }

        // Dispatch event for other components
        document.dispatchEvent(new CustomEvent('navbarPhotoUpdated', {
            detail: { photoUrl: newPhotoUrl }
        }));
    }

    /**
     * Update navbar profile name
     */
    updateNavbarName(newName) {
        const profileName = document.getElementById('dropdown-profile-name');
        if (profileName) {
            profileName.textContent = newName;
            profileName.style.color = '#10b981';
            setTimeout(() => {
                profileName.style.color = '';
            }, 2000);
        }
    }

    /**
     * Update navbar profile email
     */
    updateNavbarEmail(newEmail) {
        const profileEmail = document.getElementById('dropdown-profile-email');
        if (profileEmail) {
            profileEmail.textContent = newEmail;
            profileEmail.style.color = '#10b981';
            setTimeout(() => {
                profileEmail.style.color = '';
            }, 2000);
        }
    }

    /**
     * Update the dropdown header photo
     * @param {string} newPhotoUrl - New photo URL
     */
    updateDropdownPhoto(newPhotoUrl) {
        const dropdownPhotos = document.querySelectorAll('[class*="dropdown"] img[alt*="' + this.getCurrentUserName() + '"]');

        dropdownPhotos.forEach(photo => {
            photo.src = newPhotoUrl;
            photo.style.opacity = '0.7';
            photo.addEventListener('load', function() {
                this.style.opacity = '1';
            }, { once: true });
        });
    }

    /**
     * Get current user name from the page
     * @returns {string} Current user name
     */
    getCurrentUserName() {
        // Try to get from various sources
        const nameElement = document.querySelector('[data-user-name]');
        if (nameElement) {
            return nameElement.dataset.userName;
        }

        // Try to get from the navbar text
        const navbarNameElement = document.querySelector('.text-lg.font-bold');
        if (navbarNameElement) {
            return navbarNameElement.textContent.trim();
        }

        return 'User';
    }

    /**
     * Handle server response from profile update
     * @param {Object} response - Server response
     */
    handleProfileUpdateResponse(response) {
        if (response.success && response.user && response.user.avatar) {
            const avatarUrl = response.user.avatar.startsWith('storage/')
                ? `/storage/${response.user.avatar.replace('storage/', '')}`
                : `/storage/${response.user.avatar}`;

            this.updateNavbarPhoto(avatarUrl);
        }
    }

    /**
     * Manually trigger profile photo update
     * @param {string} photoUrl - Photo URL
     */
    triggerUpdate(photoUrl) {
        this.updateNavbarPhoto(photoUrl);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.navbarProfileUpdater = new NavbarProfileUpdater();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NavbarProfileUpdater;
}
