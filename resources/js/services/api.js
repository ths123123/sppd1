// API service for making HTTP requests

class ApiService {
    constructor(baseURL = '') {
        this.baseURL = baseURL;
        this.csrfToken = this.getCSRFToken();
    }

    getCSRFToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    getDefaultHeaders() {
        return {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfToken,
            'Accept': 'application/json'
        };
    }    async request(url, options = {}) {
        const config = {
            headers: {
                ...this.getDefaultHeaders(),
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(this.baseURL + url, config);

            if (!response.ok) {
                let errorData = {};
                try {
                    errorData = await response.json();
                } catch (e) {
                    // If response is not JSON, use status text
                    errorData.message = response.statusText || `HTTP error! status: ${response.status}`;
                }

                const error = new Error(errorData.message || `HTTP error! status: ${response.status}`);
                error.status = response.status;
                error.data = errorData;
                throw error;
            }

            // Handle empty responses
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            } else {
                return { success: true };
            }
        } catch (error) {
            console.error('API request failed:', error);

            // Enhance error with additional context
            if (!error.status) {
                error.message = 'Network error or server unavailable';
            }

            throw error;
        }
    }

    async get(url, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;

        return this.request(fullUrl, {
            method: 'GET'
        });
    }

    async post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async put(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async patch(url, data = {}) {
        return this.request(url, {
            method: 'PATCH',
            body: JSON.stringify(data)
        });
    }

    async delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }

    async postForm(url, formData) {
        return this.request(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: formData
        });
    }
}

// Travel Request API
export class TravelRequestAPI extends ApiService {
    async getAll(params = {}) {
        return this.get('/travel-requests', params);
    }

    async getById(id) {
        return this.get(`/travel-requests/${id}`);
    }

    async create(data) {
        return this.post('/travel-requests', data);
    }

    async update(id, data) {
        return this.patch(`/travel-requests/${id}`, data);
    }

    async delete(id) {
        return this.delete(`/travel-requests/${id}`);
    }

    async submit(id) {
        return this.post(`/travel-requests/${id}/submit`);
    }    async exportPDF(id) {
        try {
            const response = await fetch(`/travel-requests/${id}/export/pdf`, {
                headers: this.getDefaultHeaders()
            });

            if (!response.ok) {
                throw new Error('Failed to generate PDF');
            }

            return response.blob();
        } catch (error) {
            console.error('PDF export failed:', error);
            throw error;
        }
    }

    async calculateBudget(data) {
        return this.post('/travel-requests/calculate-budget', data);
    }
}

// Approval API
export class ApprovalAPI extends ApiService {
    async getPendingApprovals() {
        return this.get('/approval/pimpinan');
    }

    async getApprovalDetail(id) {
        return this.get(`/approval/pimpinan/${id}/show`);
    }

    async approve(id, comments = '') {
        return this.post(`/approval/pimpinan/${id}/approve`, { comments });
    }

    async reject(id, reason, comments = '') {
        return this.post(`/approval/pimpinan/${id}/reject`, { reason, comments });
    }

    async requestRevision(id, comments) {
        return this.post(`/approval/pimpinan/${id}/revision`, { comments });
    }
}

// User Management API
export class UserAPI extends ApiService {
    async getAll() {
        return this.get('/users');
    }

    async create(data) {
        return this.post('/users', data);
    }

    async toggleStatus(id) {
        return this.patch(`/users/${id}/toggle-status`);
    }    async export() {
        try {
            const response = await fetch('/users/export', {
                headers: this.getDefaultHeaders()
            });

            if (!response.ok) {
                throw new Error('Failed to export users');
            }

            return response.blob();
        } catch (error) {
            console.error('User export failed:', error);
            throw error;
        }
    }
}

// Dashboard API
export class DashboardAPI extends ApiService {
    async getStats() {
        return this.get('/dashboard/stats');
    }

    async getChartData(type) {
        return this.get(`/dashboard/chart/${type}`);
    }

    async getRecentActivities() {
        return this.get('/dashboard/recent-activities');
    }
}

// Document API
export class DocumentAPI extends ApiService {
    async getMyDocuments() {
        return this.get('/dokumen/saya');
    }

    async getAllDocuments() {
        return this.get('/dokumen/semua');
    }

    async upload(travelRequestId, files) {
        const formData = new FormData();
        formData.append('travel_request_id', travelRequestId);

        files.forEach((file, index) => {
            formData.append(`documents[${index}]`, file);
        });

        return this.postForm('/documents/upload', formData);
    }

    async delete(id) {
        return this.delete(`/documents/${id}`);
    }
}

// Export instances
export const travelRequestAPI = new TravelRequestAPI();
export const approvalAPI = new ApprovalAPI();
export const userAPI = new UserAPI();
export const dashboardAPI = new DashboardAPI();
export const documentAPI = new DocumentAPI();
