import type { Project, ProjectFormData, ProjectFilters, ApiResponse, ApiMeta } from '@/types/project';

class ProjectApiService {
    private baseUrl = '/projects';

    private getHeaders(): Record<string, string> {
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
    }

    async getProjects(filters: ProjectFilters = {}): Promise<ApiResponse<Project[]>> {
        const params = new URLSearchParams();
        if (filters.status) params.append('status', filters.status);
        if (filters.priority) params.append('priority', filters.priority);
        if (filters.search) params.append('search', filters.search);
        if (filters.sort) params.append('sort', filters.sort);
        if (filters.direction) params.append('direction', filters.direction);
        if (filters.page) params.append('page', String(filters.page));
        if (filters.per_page) params.append('per_page', String(filters.per_page));

        const url = params.toString() ? `${this.baseUrl}?${params.toString()}` : this.baseUrl;
        const response = await fetch(url, {
            headers: this.getHeaders(),
        });

        if (!response.ok) {
            throw new Error(`Failed to fetch projects (HTTP ${response.status})`);
        }

        return response.json();
    }

    async getMeta(): Promise<ApiMeta> {
        const response = await fetch(`${this.baseUrl}/meta`, {
            headers: this.getHeaders(),
        });
        if (!response.ok) {
            throw new Error('Failed to fetch metadata');
        }
        return response.json();
    }

    async getProject(id: number): Promise<{ data: Project }> {
        const response = await fetch(`${this.baseUrl}/${id}`, {
            headers: this.getHeaders(),
        });
        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            throw new Error(data.message || 'Project not found');
        }
        return response.json();
    }

    async createProject(data: ProjectFormData): Promise<{ data: Project }> {
        const response = await fetch(this.baseUrl, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(data),
        });

        const result = await response.json();
        if (!response.ok) {
            const error: any = new Error(result.message || 'Failed to create project');
            error.status = response.status;
            error.errors = result.errors || {};
            throw error;
        }

        return result;
    }

    async updateProject(id: number, data: ProjectFormData): Promise<{ data: Project }> {
        const response = await fetch(`${this.baseUrl}/${id}`, {
            method: 'PUT',
            headers: this.getHeaders(),
            body: JSON.stringify(data),
        });

        const result = await response.json();
        if (!response.ok) {
            const error: any = new Error(result.message || 'Failed to update project');
            error.status = response.status;
            error.errors = result.errors || {};
            throw error;
        }

        return result;
    }

    async deleteProject(id: number): Promise<{ message: string }> {
        const response = await fetch(`${this.baseUrl}/${id}`, {
            method: 'DELETE',
            headers: this.getHeaders(),
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Failed to delete project');
        }

        return result;
    }
}

export const projectApi = new ProjectApiService();
