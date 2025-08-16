/**
 * Navbar Access Control System
 * Mengatur kontrol akses untuk semua menu di navbar (mobile & desktop)
 */

class NavbarAccessControl {
    constructor() {
        this.userRole = this.getUserRole();
        this.init();
    }

    getUserRole() {
        // Ambil role dari meta tag atau data attribute
        const roleMeta = document.querySelector('meta[name="user-role"]');
        if (roleMeta) {
            return roleMeta.getAttribute('content');
        }

        // Fallback: cek dari data attribute pada body
        const body = document.body;
        if (body.dataset.userRole) {
            return body.dataset.userRole;
        }

        // Fallback: cek dari Auth::user()->role di Blade
        const authUser = window.authUser;
        if (authUser && authUser.role) {
            return authUser.role;
        }

        return 'staff'; // Default role
    }

    init() {
        this.setupAccessControl();
        this.setupClickHandlers();
        this.setupVisualFeedback();
    }

    setupAccessControl() {
        // Daftar role yang diizinkan untuk setiap fitur
        const rolePermissions = {
            'kasubbag': ['kasubbag'],
            'approver': ['kasubbag', 'sekretaris', 'ppk'],
            'view_all_sppd': ['admin', 'kasubbag', 'sekretaris', 'ppk'],
            'analytics': ['kasubbag', 'sekretaris', 'ppk'],
            'document_management': ['admin', 'kasubbag', 'sekretaris', 'ppk'],
            'user_management': ['admin', 'kasubbag', 'sekretaris', 'ppk']
        };

        // Terapkan kontrol akses untuk semua link dengan data-requires-role
        document.querySelectorAll('a[data-requires-role]').forEach(link => {
            const requiredRole = link.getAttribute('data-requires-role');
            const allowedRoles = rolePermissions[requiredRole] || [];

            if (!allowedRoles.includes(this.userRole)) {
                this.restrictAccess(link);
            }
        });
    }

    restrictAccess(element) {
        // Tambahkan class untuk visual feedback
        element.classList.add('opacity-50', 'cursor-not-allowed');

        // Tambahkan title untuk informasi
        const currentTitle = element.getAttribute('title') || '';
        const restrictionMessage = 'Anda tidak memiliki akses ke fitur ini';
        element.setAttribute('title', currentTitle ? `${currentTitle} - ${restrictionMessage}` : restrictionMessage);

        // Tambahkan data attribute untuk tracking
        element.setAttribute('data-access-restricted', 'true');
    }

    setupClickHandlers() {
        document.querySelectorAll('a[data-requires-role]').forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.hasAttribute('data-access-restricted')) {
                    e.preventDefault();
                    this.showUnauthorizedNotification();
                }
            });
        });
    }

    setupVisualFeedback() {
        // Tambahkan hover effect untuk item yang dibatasi
        document.querySelectorAll('a[data-access-restricted]').forEach(link => {
            // Hapus hover effect yang menyebabkan warna hitam
            link.addEventListener('mouseenter', () => {
                link.style.cursor = 'not-allowed';
                // Pastikan tidak ada background color yang berubah
                link.style.backgroundColor = 'transparent';
            });
            
            link.addEventListener('mouseleave', () => {
                link.style.cursor = 'not-allowed';
                // Pastikan background tetap transparan
                link.style.backgroundColor = 'transparent';
            });
            
            // Tambahkan CSS untuk mencegah hover effect yang tidak diinginkan
            link.style.pointerEvents = 'auto';
            link.style.userSelect = 'none';
        });
    }

    showUnauthorizedNotification() {
        // Cek apakah ada notification element yang sudah ada
        let notification = document.getElementById('unauthorized-notification');

        if (!notification) {
            // Buat notification element jika belum ada
            notification = this.createNotificationElement();
            document.body.appendChild(notification);
        }

        // Tampilkan notification
        notification.classList.remove('hidden');

        // Auto-hide setelah 5 detik
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 5000);
    }

    createNotificationElement() {
        const notification = document.createElement('div');
        notification.id = 'unauthorized-notification';
        notification.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ease-in-out';
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <div>
                    <p class="font-semibold">Akses Dibatasi</p>
                    <p class="text-sm opacity-90">Anda tidak memiliki otoritas untuk mengakses fitur ini</p>
                </div>
                <button onclick="this.parentElement.parentElement.classList.add('hidden')" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;

        return notification;
    }

    // Method untuk refresh kontrol akses (jika ada perubahan role)
    refresh() {
        this.userRole = this.getUserRole();
        this.init();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.navbarAccessControl = new NavbarAccessControl();
});

// Export untuk penggunaan global
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NavbarAccessControl;
}
