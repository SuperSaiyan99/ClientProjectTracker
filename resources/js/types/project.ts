export type ProjectStatus = 'Planning' | 'In Progress' | 'On Hold' | 'Completed';
export type ProjectPriority = 'Low' | 'Medium' | 'High';

export interface Project {
    id: number;
    client_name: string;
    project_name: string;
    description: string | null;
    status: ProjectStatus;
    priority: ProjectPriority;
    start_date: string;
    due_date: string;
    created_at?: string;
    updated_at?: string;
}

export interface ProjectFormData {
    client_name: string;
    project_name: string;
    description: string;
    status: ProjectStatus;
    priority: ProjectPriority;
    start_date: string;
    due_date: string;
}

export interface ProjectFilters {
    status?: string;
    priority?: string;
    search?: string;
    sort?: string;
    direction?: 'asc' | 'desc';
    page?: number;
    per_page?: number;
}

export interface ProjectStats {
    total: number;
    planning: number;
    inProgress: number;
    onHold: number;
    completed: number;
}

export interface ApiMeta {
    statuses: ProjectStatus[];
    priorities: ProjectPriority[];
    stats?: ProjectStats;
    current_page?: number;
    last_page?: number;
    per_page?: number;
    total?: number;
    from?: number;
    to?: number;
}

export interface ApiResponse<T> {
    data: T;
    meta?: ApiMeta;
}

export interface ApiValidationError {
    message: string;
    errors: Record<string, string[]>;
}
