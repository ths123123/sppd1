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
    }

    setupAccessControl() {
        // Daftar role yang diizinkan untuk setiap fitur
        const rolePermissions = {
            'kasubbag': ['kasubbag'],
            'approver': ['sekretaris', 'ppk'],
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
        // Tambahkan data attribute untuk tracking
        element.setAttribute('data-access-restricted', 'true');

        // Tambahkan gaya visual yang sama seperti user staff
        element.classList.add('opacity-50', 'cursor-not-allowed');

        // Hapus hover effects untuk menu yang dibatasi
        element.classList.remove('hover:text-gray-200');

        // Tambahkan event listener untuk mencegah hover effects
        element.addEventListener('mouseenter', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });
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

    showUnauthorizedNotification() {
        // Use the global showAccessWarning function for consistency
        if (typeof window.showAccessWarning === 'function') {
            // Consistent message for all restricted roles
            let message = 'Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag';

            // Use the same message for all roles that don't have access
            window.showAccessWarning(message);
        } else {
            // Fallback to simple alert if function not available
            let message = 'Anda tidak memiliki akses ke menu ini silahkan hubungi kasubbag';
            alert(message);
        }
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
