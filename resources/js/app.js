import './bootstrap';
import Alpine from 'alpinejs';

// Import modular components
import { Modal } from './components/modal.js';
import { FileUpload } from './components/file-upload.js';

// Import utilities
import * as helpers from './utils/helpers.js';

// Import API services
import * as apiServices from './services/api.js';

// Import navbar profile updater
import './navbar-profile-update.js';

// SwiperJS for Dashboard Info Card
// import Swiper, { Navigation, Pagination, EffectSlide } from 'swiper';
// import 'swiper/swiper-bundle.min.css';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';


// Make Alpine available globally
window.Alpine = Alpine;

// Make utilities available globally
window.helpers = helpers;
window.api = apiServices;

// Make components available globally
window.Modal = Modal;
window.FileUpload = FileUpload;

// Initialize Alpine
Alpine.start();

// Global error handler
window.addEventListener('error', (event) => {
    console.error('JavaScript Error:', event.error);
});

// Global unhandled promise rejection handler
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason);
    event.preventDefault(); // Prevent default unhandled rejection behavior
});

// Initialize global features
document.addEventListener('DOMContentLoaded', () => {
    // Initialize tooltips
    initializeTooltips();

    // Initialize global keyboard shortcuts
    initializeKeyboardShortcuts();

    // Initialize auto-save functionality
    initializeAutoSave();

    // (hapus juga inisialisasi Swiper jika masih ada)
});

// Custom Interactive Slider for SPPD Info Card

document.addEventListener('DOMContentLoaded', function() {
    const slidesContainer = document.getElementById('slides-container');
    const dots = document.querySelectorAll('.slider-dot');
    const prevBtn = document.getElementById('prev-slide');
    const nextBtn = document.getElementById('next-slide');
    if (!slidesContainer || !dots.length || !prevBtn || !nextBtn) return;

    let currentSlide = 0;
    const totalSlides = dots.length;

    function updateSlider() {
        slidesContainer.style.transform = `translateX(-${currentSlide * 100}%)`;
        dots.forEach((dot, index) => {
            if (index === currentSlide) {
                dot.classList.add('opacity-100');
                dot.classList.remove('opacity-50');
            } else {
                dot.classList.add('opacity-50');
                dot.classList.remove('opacity-100');
            }
        });
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        updateSlider();
    }

    function goToSlide(slideIndex) {
        currentSlide = slideIndex;
        updateSlider();
    }

    nextBtn.addEventListener('click', nextSlide);
    prevBtn.addEventListener('click', prevSlide);
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => goToSlide(index));
    });

    // Hapus auto-slide dan pause on hover
    // let autoSlideInterval = setInterval(nextSlide, 8000);
    // slidesContainer.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
    // slidesContainer.addEventListener('mouseleave', () => autoSlideInterval = setInterval(nextSlide, 8000));

    // Touch/swipe support for mobile
    let startX = 0;
    let isDragging = false;
    slidesContainer.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
    });
    slidesContainer.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
    });
    slidesContainer.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        const endX = e.changedTouches[0].clientX;
        const diffX = startX - endX;
        if (Math.abs(diffX) > 50) {
            if (diffX > 0) nextSlide();
            else prevSlide();
        }
        isDragging = false;
    });

    // Mouse drag support for desktop
    let isMouseDown = false;
    let mouseStartX = 0;
    slidesContainer.addEventListener('mousedown', (e) => {
        isMouseDown = true;
        mouseStartX = e.clientX;
        slidesContainer.style.cursor = 'grabbing';
    });
    slidesContainer.addEventListener('mousemove', (e) => {
        if (!isMouseDown) return;
        e.preventDefault();
    });
    slidesContainer.addEventListener('mouseup', (e) => {
        if (!isMouseDown) return;
        const mouseEndX = e.clientX;
        const diffX = mouseStartX - mouseEndX;
        if (Math.abs(diffX) > 50) {
            if (diffX > 0) nextSlide();
            else prevSlide();
        }
        isMouseDown = false;
        slidesContainer.style.cursor = 'grab';
    });
    slidesContainer.style.cursor = 'grab';

    // Prevent text selection during drag
    slidesContainer.addEventListener('dragstart', (e) => e.preventDefault());

    // Responsive: hide arrows on mobile
    function handleResize() {
        if (window.innerWidth <= 768) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = '';
            nextBtn.style.display = '';
        }
    }
    window.addEventListener('resize', handleResize);
    handleResize();

    updateSlider();
});

function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[title]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const element = event.target;
    const text = element.getAttribute('title');
    if (!text) return;

    // Remove title to prevent default tooltip
    element.removeAttribute('title');
    element.setAttribute('data-original-title', text);

    // Create tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    tooltip.textContent = text;
    
    // Set styles individually instead of using template literal
    tooltip.style.position = 'absolute';
    tooltip.style.background = 'rgba(0, 0, 0, 0.8)';
    tooltip.style.color = 'white';
    tooltip.style.padding = '8px 12px';
    tooltip.style.borderRadius = '6px';
    tooltip.style.fontSize = '14px';
    tooltip.style.zIndex = '1000';
    tooltip.style.pointerEvents = 'none';
    tooltip.style.whiteSpace = 'nowrap';

    document.body.appendChild(tooltip);

    // Position tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';

    // Add to element for later removal
    element._tooltip = tooltip;
}

function hideTooltip(event) {
    const element = event.target;
    if (element._tooltip) {
        element._tooltip.remove();
        element._tooltip = null;
    }

    // Restore original title
    const originalTitle = element.getAttribute('data-original-title');
    if (originalTitle) {
        element.setAttribute('title', originalTitle);
        element.removeAttribute('data-original-title');
    }
}

function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', (event) => {
        // Ctrl/Cmd + S for save
        if ((event.ctrlKey || event.metaKey) && event.key === 's') {
            event.preventDefault();
            const saveButton = document.querySelector('[data-save-shortcut]');
            if (saveButton && !saveButton.disabled) {
                saveButton.click();
            }
        }

        // Escape key for closing modals
        if (event.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal && openModal._modalInstance) {
                openModal._modalInstance.hide();
            }
        }
    });
}

function initializeAutoSave() {
    const autoSaveForms = document.querySelectorAll('[data-auto-save]');

    autoSaveForms.forEach(form => {
        const interval = parseInt(form.getAttribute('data-auto-save')) || 30000; // 30 seconds default

        setInterval(() => {
            try {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                // Save to localStorage with timestamp
                const saveKey = `autosave_${form.id || 'form'}_${Date.now()}`;
                localStorage.setItem(saveKey, JSON.stringify({
                    data,
                    timestamp: Date.now()
                }));

                // Keep only last 5 auto-saves
                const allSaves = Object.keys(localStorage)
                    .filter(key => key.startsWith(`autosave_${form.id || 'form'}_`))
                    .sort();

                if (allSaves.length > 5) {
                    allSaves.slice(0, -5).forEach(key => {
                        localStorage.removeItem(key);
                    });
                }
            } catch (error) {
                console.warn('Auto-save failed:', error);
            }
        }, interval);
    });
}
