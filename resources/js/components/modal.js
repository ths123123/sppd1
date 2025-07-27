// Modal component functionality

export class Modal {
    constructor(elementOrId, options = {}) {
        this.element = typeof elementOrId === 'string'
            ? document.getElementById(elementOrId)
            : elementOrId;

        if (!this.element) {
            throw new Error('Modal element not found');
        }

        this.options = {
            backdrop: true,
            keyboard: true,
            focus: true,
            ...options
        };

        this.isShown = false;
        this.init();
    }

    init() {
        // Add event listeners
        this.element.addEventListener('click', this.handleBackdropClick.bind(this));

        if (this.options.keyboard) {
            document.addEventListener('keydown', this.handleKeydown.bind(this));
        }

        // Find close buttons
        const closeButtons = this.element.querySelectorAll('[data-modal-close]');
        closeButtons.forEach(button => {
            button.addEventListener('click', () => this.hide());
        });
    }    show() {
        if (this.isShown) return;

        try {
            this.isShown = true;
            this.element.style.display = 'flex';

            // Add a small delay to ensure display:flex is applied before adding the class
            requestAnimationFrame(() => {
                this.element.classList.add('show');
            });

            if (this.options.focus) {
                this.element.focus();
            }

            // Add backdrop
            document.body.classList.add('modal-open');

            // Emit event
            this.element.dispatchEvent(new CustomEvent('modal:shown'));
        } catch (error) {
            console.error('Error showing modal:', error);
            this.isShown = false;
        }
    }

    hide() {
        if (!this.isShown) return;

        try {
            this.isShown = false;
            this.element.classList.remove('show');

            setTimeout(() => {
                this.element.style.display = 'none';
                document.body.classList.remove('modal-open');

                // Emit event
                this.element.dispatchEvent(new CustomEvent('modal:hidden'));
            }, 300); // Match transition duration
        } catch (error) {
            console.error('Error hiding modal:', error);
        }
    }

    toggle() {
        this.isShown ? this.hide() : this.show();
    }

    handleBackdropClick(event) {
        if (this.options.backdrop && event.target === this.element) {
            this.hide();
        }
    }

    handleKeydown(event) {
        if (this.isShown && event.key === 'Escape') {
            this.hide();
        }
    }

    setContent(content) {
        const modalBody = this.element.querySelector('.modal-body');
        if (modalBody) {
            modalBody.innerHTML = content;
        }
    }

    setTitle(title) {
        const modalTitle = this.element.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = title;
        }
    }

    static confirm(options = {}) {
        return new Promise((resolve) => {
            const config = {
                title: 'Konfirmasi',
                message: 'Apakah Anda yakin?',
                confirmText: 'Ya',
                cancelText: 'Batal',
                confirmClass: 'btn btn-primary',
                cancelClass: 'btn btn-secondary',
                ...options
            };

            // Create modal HTML
            const modalHTML = `
                <div class="modal" id="confirmModal" tabindex="-1">
                    <div class="modal-backdrop"></div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${config.title}</h5>
                        </div>
                        <div class="modal-body">
                            <p>${config.message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="${config.cancelClass}" data-modal-close>
                                ${config.cancelText}
                            </button>
                            <button type="button" class="${config.confirmClass}" id="confirmButton">
                                ${config.confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Add to DOM
            document.body.insertAdjacentHTML('beforeend', modalHTML);

            const modalElement = document.getElementById('confirmModal');
            const modal = new Modal(modalElement);

            // Handle confirm button
            const confirmButton = modalElement.querySelector('#confirmButton');
            confirmButton.addEventListener('click', () => {
                modal.hide();
                resolve(true);
            });

            // Handle modal close
            modalElement.addEventListener('modal:hidden', () => {
                document.body.removeChild(modalElement);
                resolve(false);
            });

            modal.show();
        });
    }
}

// Auto-initialize modals
document.addEventListener('DOMContentLoaded', () => {
    try {
        // Initialize modal triggers
        const modalTriggers = document.querySelectorAll('[data-modal-target]');
        modalTriggers.forEach(trigger => {
            const targetId = trigger.getAttribute('data-modal-target');
            
            // Try to find by ID first, then by name attribute
            let targetElement = document.getElementById(targetId);
            
            // If not found by ID, try to find by name
            if (!targetElement) {
                targetElement = document.querySelector(`[name="${targetId}"]`);
            }

            if (targetElement) {
                const modal = new Modal(targetElement);

                // Store modal instance on the element for global access
                targetElement._modalInstance = modal;

                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    modal.show();
                });
            } else {
                console.warn(`Modal target element not found: ${targetId}. Will try again when DOM fully loaded.`);
                
                // Try again after a delay to ensure DOM is fully loaded
                setTimeout(() => {
                    let delayedTarget = document.getElementById(targetId);
                    if (!delayedTarget) {
                        delayedTarget = document.querySelector(`[name="${targetId}"]`);
                    }
                    
                    if (delayedTarget) {
                        const modal = new Modal(delayedTarget);
                        delayedTarget._modalInstance = modal;
                        
                        // Re-attach event listener to triggers
                        document.querySelectorAll(`[data-modal-target="${targetId}"]`).forEach(t => {
                            t.addEventListener('click', (e) => {
                                e.preventDefault();
                                modal.show();
                            });
                        });
                        
                        console.log(`Modal ${targetId} initialized after delay`);
                    } else {
                        console.warn(`Modal target element still not found after delay: ${targetId}`);
                    }
                }, 1000);
            }
        });

        // Add global event listeners for Alpine.js compatibility
        document.addEventListener('open-modal', (e) => {
            const targetId = e.detail;
            let targetElement = document.getElementById(targetId);
            if (!targetElement) {
                targetElement = document.querySelector(`[name="${targetId}"]`);
            }
            
            if (targetElement && targetElement._modalInstance) {
                targetElement._modalInstance.show();
            }
        });
        
        document.addEventListener('close-modal', (e) => {
            const targetId = e.detail;
            let targetElement = document.getElementById(targetId);
            if (!targetElement) {
                targetElement = document.querySelector(`[name="${targetId}"]`);
            }
            
            if (targetElement && targetElement._modalInstance) {
                targetElement._modalInstance.hide();
            }
        });

        console.log('Modal components initialized successfully');
    } catch (error) {
        console.error('Error initializing modal components:', error);
    }
});
