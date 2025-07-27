@if(session('success') || session('error') || session('warning') || session('info'))
<div id="toast-container"
     class="fixed top-20 left-1/2 z-50 space-y-4 max-w-xs w-full transform -translate-x-1/2 px-2"
     style="pointer-events: none;">
    <style>
        #toast-container {
            right: auto !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            top: 5.5rem !important; /* Jarak aman dari navbar (sekitar 88px) */
            max-width: 20rem !important;
            width: 100% !important;
            padding: 0 0.5rem !important;
        }
        .toast-notification {
            padding: 0.85rem 1.25rem !important;
            font-size: 1rem !important;
            min-width: 0 !important;
            box-shadow: 0 2px 12px rgba(0,0,0,0.13) !important;
        }
        @media (max-width: 640px) {
            #toast-container {
                top: 4.2rem !important; /* Lebih kecil di mobile, tetap di bawah navbar */
            }
        }
    </style>
    @if(session('success'))
    <div class="bg-green-50 border border-green-400 rounded-xl shadow-xl p-4 flex items-start gap-3 animate-toast-in toast-notification">
        <div class="pt-1">
            <i class="fas fa-check-circle text-green-500 text-lg"></i>
        </div>
        <div class="flex-1">
            <div class="font-semibold text-base text-green-800">Berhasil!</div>
            <div class="text-sm text-green-700 mt-0.5">{{ session('success') }}</div>
        </div>
        <button onclick="this.closest('.toast-notification').remove()" class="ml-2 p-2 rounded-full hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-300 transition">
            <i class="fas fa-times text-green-600 text-base"></i>
        </button>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-400 rounded-xl shadow-xl p-4 flex items-start gap-3 animate-toast-in toast-notification">
        <div class="pt-1">
            <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
        </div>
        <div class="flex-1">
            <div class="font-semibold text-base text-red-800">Terjadi Kesalahan!</div>
            <div class="text-sm text-red-700 mt-0.5">{{ session('error') }}</div>
        </div>
        <button onclick="this.closest('.toast-notification').remove()" class="ml-2 p-2 rounded-full hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-300 transition">
            <i class="fas fa-times text-red-600 text-base"></i>
        </button>
    </div>
    @endif
    @if(session('warning'))
    <div class="bg-yellow-50 border border-yellow-400 rounded-xl shadow-xl p-4 flex items-start gap-3 animate-toast-in toast-notification">
        <div class="pt-1">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-lg"></i>
        </div>
        <div class="flex-1">
            <div class="font-semibold text-base text-yellow-800">Peringatan!</div>
            <div class="text-sm text-yellow-700 mt-0.5">{{ session('warning') }}</div>
        </div>
        <button onclick="this.closest('.toast-notification').remove()" class="ml-2 p-2 rounded-full hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-300 transition">
            <i class="fas fa-times text-yellow-600 text-base"></i>
        </button>
    </div>
    @endif
    @if(session('info'))
    <div class="bg-blue-50 border border-blue-400 rounded-xl shadow-xl p-4 flex items-start gap-3 animate-toast-in toast-notification">
        <div class="pt-1">
            <i class="fas fa-info-circle text-blue-500 text-lg"></i>
        </div>
        <div class="flex-1">
            <div class="font-semibold text-base text-blue-800">Informasi</div>
            <div class="text-sm text-blue-700 mt-0.5">{{ session('info') }}</div>
        </div>
        <button onclick="this.closest('.toast-notification').remove()" class="ml-2 p-2 rounded-full hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-300 transition">
            <i class="fas fa-times text-blue-600 text-base"></i>
        </button>
    </div>
    @endif
</div>
<style>
@keyframes toast-in {
  from { opacity: 0; transform: translateY(-24px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-toast-in { animation: toast-in 0.5s cubic-bezier(0.4,0,0.2,1); }
</style>
<script>
// Auto-hide toast notifications after 5 seconds
if (typeof window !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function() {
        const toasts = document.querySelectorAll('.toast-notification');
        toasts.forEach(toast => {
            setTimeout(() => {
                toast.style.transition = 'opacity 0.5s, transform 0.5s';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-16px) scale(0.98)';
                setTimeout(() => {
                    toast.remove();
                }, 500);
            }, 5000);
        });
    });
}
</script>
@endif 