// File upload component

export class FileUpload {
    constructor(element, options = {}) {
        this.element = typeof element === 'string'
            ? document.querySelector(element)
            : element;

        if (!this.element) {
            throw new Error('File upload element not found');
        }

        this.options = {
            multiple: true,
            accept: '.pdf,.doc,.docx,.jpg,.jpeg,.png',
            maxSize: 5 * 1024 * 1024, // 5MB
            maxFiles: 10,
            dragAndDrop: true,
            ...options
        };

        this.files = [];
        this.init();
    }

    init() {
        this.createUploadArea();
        this.bindEvents();
    }

    createUploadArea() {
        this.element.innerHTML = `
            <div class="sppd-file-upload" id="fileUploadArea">
                <div class="sppd-file-upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="sppd-file-upload-text">
                    Klik untuk memilih file atau seret file ke sini
                </div>
                <div class="sppd-file-upload-hint">
                    Format yang didukung: PDF, DOC, DOCX, JPG, PNG (Max: 5MB per file)
                </div>
                <input type="file"
                       id="fileInput"
                       ${this.options.multiple ? 'multiple' : ''}
                       accept="${this.options.accept}"
                       style="display: none;">
            </div>
            <div class="sppd-file-list" id="fileList"></div>
        `;

        this.uploadArea = this.element.querySelector('#fileUploadArea');
        this.fileInput = this.element.querySelector('#fileInput');
        this.fileList = this.element.querySelector('#fileList');
    }

    bindEvents() {
        // Click to select files
        this.uploadArea.addEventListener('click', () => {
            this.fileInput.click();
        });

        // File input change
        this.fileInput.addEventListener('change', (e) => {
            this.handleFiles(Array.from(e.target.files));
        });

        if (this.options.dragAndDrop) {
            // Drag and drop events
            this.uploadArea.addEventListener('dragover', this.handleDragOver.bind(this));
            this.uploadArea.addEventListener('dragleave', this.handleDragLeave.bind(this));
            this.uploadArea.addEventListener('drop', this.handleDrop.bind(this));
        }
    }

    handleDragOver(e) {
        e.preventDefault();
        this.uploadArea.classList.add('dragover');
    }

    handleDragLeave(e) {
        e.preventDefault();
        this.uploadArea.classList.remove('dragover');
    }

    handleDrop(e) {
        e.preventDefault();
        this.uploadArea.classList.remove('dragover');

        const files = Array.from(e.dataTransfer.files);
        this.handleFiles(files);
    }    handleFiles(newFiles) {
        try {
            const validFiles = [];

            for (const file of newFiles) {
                // Check file count
                if (this.files.length + validFiles.length >= this.options.maxFiles) {
                    this.showError(`Maksimal ${this.options.maxFiles} file dapat diunggah.`);
                    break;
                }

                // Check file size
                if (file.size > this.options.maxSize) {
                    this.showError(`File ${file.name} terlalu besar. Maksimal 5MB.`);
                    continue;
                }

                // Check file type
                if (!this.isValidFileType(file)) {
                    this.showError(`File ${file.name} tidak didukung.`);
                    continue;
                }

                // Check duplicate
                if (this.isDuplicate(file)) {
                    this.showError(`File ${file.name} sudah ada.`);
                    continue;
                }

                validFiles.push(file);
            }

            // Add valid files
            validFiles.forEach(file => {
                this.addFile(file);
            });

            // Reset input
            this.fileInput.value = '';

            // Emit event
            this.element.dispatchEvent(new CustomEvent('files:changed', {
                detail: { files: this.files }
            }));
        } catch (error) {
            console.error('Error handling files:', error);
            this.showError('Terjadi kesalahan saat memproses file.');
        }
    }

    addFile(file) {
        const fileData = {
            id: Date.now() + Math.random(),
            file: file,
            name: file.name,
            size: file.size,
            type: file.type
        };

        this.files.push(fileData);
        this.renderFileList();
    }

    removeFile(fileId) {
        this.files = this.files.filter(f => f.id !== fileId);
        this.renderFileList();

        // Emit event
        this.element.dispatchEvent(new CustomEvent('files:changed', {
            detail: { files: this.files }
        }));
    }

    renderFileList() {
        if (this.files.length === 0) {
            this.fileList.innerHTML = '';
            return;
        }

        const filesHTML = this.files.map(fileData => `
            <div class="sppd-file-item">
                <div class="sppd-file-info">
                    <div class="sppd-file-icon">
                        <i class="fas ${this.getFileIcon(fileData.type)}"></i>
                    </div>
                    <div>
                        <div class="sppd-file-name">${this.escapeHtml(fileData.name)}</div>
                        <div class="sppd-file-size">${this.formatFileSize(fileData.size)}</div>
                    </div>
                </div>
                <button type="button"
                        class="sppd-file-remove"
                        data-file-id="${fileData.id}"
                        title="Hapus file">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');

        this.fileList.innerHTML = filesHTML;

        // Add event listeners for remove buttons
        this.fileList.querySelectorAll('.sppd-file-remove').forEach(button => {
            button.addEventListener('click', (e) => {
                const fileId = parseInt(e.currentTarget.getAttribute('data-file-id'));
                this.removeFile(fileId);
            });
        });
    }

    // Helper method to escape HTML
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    getFileIcon(mimeType) {
        if (mimeType.includes('pdf')) return 'fa-file-pdf';
        if (mimeType.includes('word') || mimeType.includes('doc')) return 'fa-file-word';
        if (mimeType.includes('image')) return 'fa-file-image';
        return 'fa-file';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    isValidFileType(file) {
        const acceptedTypes = this.options.accept.split(',').map(t => t.trim());
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

        return acceptedTypes.some(type => {
            if (type.startsWith('.')) {
                return type === fileExtension;
            }
            return file.type.includes(type.replace('*', ''));
        });
    }

    isDuplicate(newFile) {
        return this.files.some(f =>
            f.file.name === newFile.name &&
            f.file.size === newFile.size
        );
    }

    showError(message) {
        // Create error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'notification notification-error fade-in';
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 1000;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        `;
        errorDiv.textContent = message;

        document.body.appendChild(errorDiv);

        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    getFiles() {
        return this.files.map(f => f.file);
    }

    getFileData() {
        return this.files;
    }

    clear() {
        this.files = [];
        this.renderFileList();

        // Emit event
        this.element.dispatchEvent(new CustomEvent('files:changed', {
            detail: { files: this.files }
        }));
    }
}

// Auto-initialize file uploads
document.addEventListener('DOMContentLoaded', () => {
    try {
        const fileUploadElements = document.querySelectorAll('[data-file-upload]');
        fileUploadElements.forEach(element => {
            try {
                const options = JSON.parse(element.getAttribute('data-file-upload') || '{}');
                const fileUpload = new FileUpload(element, options);

                // Store instance on element for global access
                element._fileUploadInstance = fileUpload;
            } catch (error) {
                console.error('Error initializing file upload element:', error);
            }
        });

        console.log('File upload components initialized successfully');
    } catch (error) {
        console.error('Error initializing file upload components:', error);
    }
});
