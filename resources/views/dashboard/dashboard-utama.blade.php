{{--
    ====================================================================
    DASHBOARD MAIN LAYOUT - SISTEM SPPD KPU KABUPATEN CIREBON
    ====================================================================

    🎯 PROFESSIONAL CODE STRUCTURE - PHASE 2 COMPLETED

    📁 MODULAR COMPONENTS:
    ├── partials/header.blade.php       → Dashboard header & title
    ├── partials/statistics.blade.php   → Statistics cards
    ├── partials/charts.blade.php       → Chart visualizations
    ├── partials/quick-actions.blade.php → Action shortcuts
    └── assets/charts.js                → JavaScript module

    ✅ BENEFITS:
    - Clean & organized structure
    - Reusable components
    - Separated JavaScript logic
    - Better performance
    - Easier maintenance

    📊 ARCHITECTURE:
    - Container: max-w-7xl mx-auto
    - Grid layouts: responsive design
    - Consistent spacing & styling
    - Professional UI components

    ====================================================================
--}}

@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Dashboard Header --}}
        @include('dashboard.partials.header')

        {{-- Statistics Cards --}}
        @include('dashboard.partials.statistics')

        {{-- Charts Section --}}
        @include('dashboard.partials.charts')

        {{-- Quick Actions --}}
        {{-- @include('dashboard.partials.quick-actions') --}}
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/dashboard/charts.js'])
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Prepare data from backend
        const dashboardData = {
            months: @json($months ?? []),
            monthlyApproved: @json($monthlyApproved ?? []),
            monthlySubmitted: @json($monthlySubmitted ?? []),
            statusDistribution: {
                approved: {{ $approvedCount ?? 0 }},
                submitted: {{ $pendingCount ?? 0 }},
                in_review: {{ $reviewCount ?? 0 }},
                rejected: {{ $rejectedCount ?? 0 }},
                draft: {{ $draftCount ?? 0 }}
            }
        };

        // Initialize dashboard
        if (window.DashboardManager) {
            window.DashboardManager.init(dashboardData);
        } else {
            console.error('DashboardManager not found! Make sure charts.js is loaded.');
        }

        // Debug logging
        console.log('🎯 Dashboard SPPD KPU Kabupaten Cirebon - Phase 2 Active');
        console.log('📊 Backend data loaded:', dashboardData);
    });

    // Auto-refresh every 5 minutes (optional)
    setInterval(() => {
        console.log('♻️ Auto-refresh dashboard data...');
        // Implement AJAX refresh here if needed
    }, 300000); // 5 minutes
</script>
@endpush

@push('styles')
<style>
/* Custom scrollbar for better aesthetics */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
/* Prevent text selection during drag */
.slider-container * {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
/* Smooth transitions */
#slides-container {
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
/* Hover effects for navigation */
.slider-dot:hover {
    opacity: 0.8 !important;
}
/* Mobile responsiveness */
@media (max-width: 768px) {
    .slider-container {
        margin: 0 -1rem;
    }
    #prev-slide, #next-slide {
        display: none;
    }
}
</style>
@endpush
