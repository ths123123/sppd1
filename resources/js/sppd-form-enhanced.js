/**
 * SPPD Form Enhanced JavaScript
 * Advanced interactions and animations for professional SPPD form
 */

// Enhanced form animations and interactions
document.addEventListener('DOMContentLoaded', function() {
    initializeFormEnhancements();
    setupAdvancedInteractions();
    setupResponsiveDesign();
});

function initializeFormEnhancements() {
    // Animate form sections on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    document.querySelectorAll('.sppd-form-section').forEach(section => {
        observer.observe(section);
    });

    // Enhanced input focus effects
    setupInputFocusEffects();
    
    // Setup floating labels
    setupFloatingLabels();
    
    // Initialize tooltips
    initializeTooltips();
}

function setupInputFocusEffects() {
    const inputs = document.querySelectorAll('.sppd-form-input, .sppd-form-select, .sppd-form-textarea');
    
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
            
            // Add glow effect
            this.style.boxShadow = '0 0 20px rgba(102, 126, 234, 0.3)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('input-focused');
            
            // Remove glow effect
            this.style.boxShadow = '';
        });
    });
}

function setupFloatingLabels() {
    const inputGroups = document.querySelectorAll('.sppd-form-group');
    
    inputGroups.forEach(group => {
        const input = group.querySelector('input, select, textarea');
        const label = group.querySelector('label');
        
        if (input && label) {
            // Check if input has value on load
            if (input.value) {
                label.classList.add('floating');
            }
            
            input.addEventListener('focus', () => {
                label.classList.add('floating');
            });
            
            input.addEventListener('blur', () => {
                if (!input.value) {
                    label.classList.remove('floating');
                }
            });
        }
    });
}

function initializeTooltips() {
    // Create tooltip element
    const tooltip = document.createElement('div');
    tooltip.className = 'sppd-tooltip';
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        z-index: 1000;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
        max-width: 200px;
        text-align: center;
    `;
    document.body.appendChild(tooltip);

    // Add tooltips to info icons
    document.querySelectorAll('.sppd-text-info').forEach(info => {
        info.addEventListener('mouseenter', function(e) {
            const text = this.querySelector('span').textContent;
            tooltip.textContent = text;
            tooltip.style.opacity = '1';
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        });
        
        info.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
        });
    });
}

function setupAdvancedInteractions() {
    // Enhanced budget calculations with visual feedback
    setupBudgetCalculations();
    
    // Progressive form validation
    setupProgressiveValidation();
    
    // Smart form suggestions
    setupSmartSuggestions();
}

function setupBudgetCalculations() {
    const budgetInputs = document.querySelectorAll('#biaya_transport, #biaya_penginapan, #uang_harian, #biaya_lainnya');
    const totalDisplay = document.getElementById('total_display');
    
    budgetInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Add typing effect
            this.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
            
            setTimeout(() => {
                this.style.backgroundColor = '';
                updateTotalWithAnimation();
            }, 500);
        });
    });
    
    function updateTotalWithAnimation() {
        const total = calculateTotalBudget();
        
        if (totalDisplay) {
            // Animate total change
            totalDisplay.style.transform = 'scale(1.1)';
            totalDisplay.style.transition = 'transform 0.3s ease';
            
            setTimeout(() => {
                totalDisplay.textContent = 'Rp ' + formatRupiah(total);
                totalDisplay.style.transform = 'scale(1)';
            }, 150);
        }
    }
}

function setupProgressiveValidation() {
    const form = document.getElementById('sppd-form');
    const progressSteps = document.querySelectorAll('.sppd-progress-step');
    
    form.addEventListener('input', function() {
        updateProgressSteps();
    });
    
    function updateProgressSteps() {
        let completedSteps = 0;
        
        // Check basic info
        const basicInfo = document.getElementById('nama').value;
        if (basicInfo) completedSteps++;
        
        // Check travel details
        const travelDetails = document.getElementById('tujuan').value && document.getElementById('keperluan').value;
        if (travelDetails) completedSteps++;
        
        // Check budget
        const budget = document.getElementById('biaya_transport').value || document.getElementById('biaya_penginapan').value;
        if (budget) completedSteps++;
        
        // Check documents (if any)
        const documents = document.querySelector('input[type="file"]')?.files?.length > 0;
        if (documents) completedSteps++;
        
        // Update progress steps
        progressSteps.forEach((step, index) => {
            if (index < completedSteps) {
                step.classList.add('active');
                step.querySelector('.sppd-progress-circle').innerHTML = '<i class="fas fa-check"></i>';
            } else {
                step.classList.remove('active');
                step.querySelector('.sppd-progress-circle').innerHTML = index + 1;
            }
        });
    }
}

function setupSmartSuggestions() {
    // Smart destination suggestions based on common KPU locations
    const tujuanInput = document.getElementById('tujuan');
    const commonDestinations = [
        'KPU Provinsi Jawa Barat',
        'KPU Provinsi DKI Jakarta',
        'KPU Pusat - Jakarta',
        'KPU Kabupaten Bandung',
        'KPU Kabupaten Garut',
        'KPU Kabupaten Tasikmalaya',
        'KPU Kabupaten Cianjur',
        'KPU Kabupaten Sukabumi'
    ];
    
    if (tujuanInput) {
        setupAutoComplete(tujuanInput, commonDestinations);
    }
    
    // Smart purpose suggestions
    const keperluanInput = document.getElementById('keperluan');
    const commonPurposes = [
        'Koordinasi kegiatan pemilihan',
        'Rapat koordinasi dengan KPU Provinsi',
        'Bimbingan teknis (Bimtek)',
        'Sosialisasi peraturan KPU',
        'Monitoring dan evaluasi',
        'Pelatihan teknis',
        'Rapat pleno KPU'
    ];
    
    if (keperluanInput) {
        setupAutoComplete(keperluanInput, commonPurposes);
    }
}

function setupAutoComplete(input, suggestions) {
    const container = document.createElement('div');
    container.className = 'sppd-autocomplete';
    container.style.cssText = `
        position: relative;
        display: inline-block;
        width: 100%;
    `;
    
    const suggestionList = document.createElement('div');
    suggestionList.className = 'sppd-suggestions';
    suggestionList.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    `;
    
    input.parentNode.insertBefore(container, input);
    container.appendChild(input);
    container.appendChild(suggestionList);
    
    input.addEventListener('input', function() {
        const value = this.value.toLowerCase();
        suggestionList.innerHTML = '';
        
        if (value.length > 0) {
            const filtered = suggestions.filter(s => s.toLowerCase().includes(value));
            
            if (filtered.length > 0) {
                filtered.forEach(suggestion => {
                    const item = document.createElement('div');
                    item.style.cssText = `
                        padding: 8px 12px;
                        cursor: pointer;
                        border-bottom: 1px solid #f3f4f6;
                    `;
                    item.textContent = suggestion;
                    
                    item.addEventListener('click', function() {
                        input.value = suggestion;
                        suggestionList.style.display = 'none';
                    });
                    
                    item.addEventListener('mouseenter', function() {
                        this.style.backgroundColor = '#f3f4f6';
                    });
                    
                    item.addEventListener('mouseleave', function() {
                        this.style.backgroundColor = '';
                    });
                    
                    suggestionList.appendChild(item);
                });
                
                suggestionList.style.display = 'block';
            } else {
                suggestionList.style.display = 'none';
            }
        } else {
            suggestionList.style.display = 'none';
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) {
            suggestionList.style.display = 'none';
        }
    });
}

function setupResponsiveDesign() {
    // Handle mobile responsiveness
    const handleResize = () => {
        const isMobile = window.innerWidth < 768;
        const sections = document.querySelectorAll('.sppd-form-section');
        
        sections.forEach(section => {
            if (isMobile) {
                section.style.padding = '1.5rem';
                section.style.margin = '0 0.5rem 1.5rem';
            } else {
                section.style.padding = '';
                section.style.margin = '';
            }
        });
    };
    
    window.addEventListener('resize', handleResize);
    handleResize(); // Initial call
}

// Helper functions
function calculateTotalBudget() {
    const transport = parseNumber(document.getElementById('biaya_transport')?.value || '0');
    const penginapan = parseNumber(document.getElementById('biaya_penginapan')?.value || '0');
    const harian = parseNumber(document.getElementById('uang_harian')?.value || '0');
    const lainnya = parseNumber(document.getElementById('biaya_lainnya')?.value || '0');
    
    return transport + penginapan + harian + lainnya;
}

// Add CSS animations dynamically
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        animation: slideInFromBottom 0.6s ease-out forwards;
    }
    
    @keyframes slideInFromBottom {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .input-focused {
        transform: scale(1.02);
        transition: transform 0.3s ease;
    }
    
    .floating {
        transform: translateY(-20px) scale(0.85);
        color: #667eea !important;
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(style);
