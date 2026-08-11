<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import type { Project, ProjectFormData, ProjectFilters, ProjectStatus, ProjectPriority } from '@/types/project';
import { projectApi } from '@/services/projectApi';
import ProjectTable from '@/components/projects/ProjectTable.vue';
import ProjectCardGrid from '@/components/projects/ProjectCardGrid.vue';
import ProjectFiltersComponent from '@/components/projects/ProjectFilters.vue';
import ProjectFormModal from '@/components/projects/ProjectFormModal.vue';
import ConfirmDeleteModal from '@/components/projects/ConfirmDeleteModal.vue';
import ToastNotification, { type ToastItem } from '@/components/projects/ToastNotification.vue';
import SkeletonLoader from '@/components/projects/SkeletonLoader.vue';
import EmptyState from '@/components/projects/EmptyState.vue';
import Pagination from '@/components/projects/Pagination.vue';
import { Plus, Briefcase, CheckCircle2, Clock, PauseCircle, ListTodo, Layers } from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: '/dashboard',
            },
            {
                title: 'Project Tracker',
                href: '/dashboard',
            },
        ],
    },
});

// State
const filteredProjects = ref<Project[]>([]);
const statuses = ref<ProjectStatus[]>(['Planning', 'In Progress', 'On Hold', 'Completed']);
const priorities = ref<ProjectPriority[]>(['Low', 'Medium', 'High']);
const isLoading = ref<boolean>(true);
const isSubmitting = ref<boolean>(false);
const isDeleting = ref<boolean>(false);
const viewMode = ref<'table' | 'grid'>('table');

// Pagination State
const pagination = ref({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
    from: 0,
    to: 0,
});

// Summary Stats State
const stats = ref({
    total: 0,
    planning: 0,
    inProgress: 0,
    onHold: 0,
    completed: 0,
});

// Filters
const filters = ref<ProjectFilters>({
    search: '',
    status: '',
    priority: '',
    sort: 'created_at',
    direction: 'desc',
});

// Modals
const isFormModalOpen = ref<boolean>(false);
const selectedProject = ref<Project | null>(null);
const serverErrors = ref<Record<string, string[]>>({});

const isDeleteModalOpen = ref<boolean>(false);
const projectToDelete = ref<Project | null>(null);

// Toasts
const toasts = ref<ToastItem[]>([]);
let toastIdCounter = 0;

function addToast(type: 'success' | 'error' | 'info', message: string) {
    const id = ++toastIdCounter;
    toasts.value.push({ id, type, message });
    setTimeout(() => {
        dismissToast(id);
    }, 4000);
}

function dismissToast(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id);
}

// Fetch Projects from API
async function fetchProjects(page = 1) {
    isLoading.value = true;
    try {
        const response = await projectApi.getProjects({
            ...filters.value,
            page,
            per_page: pagination.value.perPage,
        });
        filteredProjects.value = response.data;

        if (response.meta) {
            if (response.meta.statuses) statuses.value = response.meta.statuses;
            if (response.meta.priorities) priorities.value = response.meta.priorities;
            if (response.meta.stats) stats.value = response.meta.stats;

            // Laravel LengthAwarePaginator metadata:
            pagination.value.currentPage = response.meta.current_page || 1;
            pagination.value.lastPage = response.meta.last_page || 1;
            pagination.value.total = response.meta.total || 0;
            pagination.value.from = response.meta.from || 0;
            pagination.value.to = response.meta.to || 0;
        }
    } catch (err: any) {
        addToast('error', err.message || 'Failed to load projects.');
    } finally {
        isLoading.value = false;
    }
}

const hasActiveFilters = computed(() => {
    return !!(filters.value.search || filters.value.status || filters.value.priority || (filters.value.sort && filters.value.sort !== 'created_at'));
});

// Handlers
function handleFilterUpdate(newFilters: ProjectFilters) {
    filters.value = newFilters;
    fetchProjects(1);
}

function handleFilterReset() {
    filters.value = {
        search: '',
        status: '',
        priority: '',
        sort: 'created_at',
        direction: 'desc',
    };
    fetchProjects(1);
}

function openCreateModal() {
    selectedProject.value = null;
    serverErrors.value = {};
    isFormModalOpen.value = true;
}

function openEditModal(project: Project) {
    selectedProject.value = project;
    serverErrors.value = {};
    isFormModalOpen.value = true;
}

function closeFormModal() {
    isFormModalOpen.value = false;
    selectedProject.value = null;
    serverErrors.value = {};
}

async function handleFormSubmit(formData: ProjectFormData) {
    isSubmitting.value = true;
    serverErrors.value = {};

    try {
        if (selectedProject.value) {
            await projectApi.updateProject(selectedProject.value.id, formData);
            addToast('success', `Project "${formData.project_name}" updated.`);
        } else {
            await projectApi.createProject(formData);
            addToast('success', `Project "${formData.project_name}" created.`);
        }

        closeFormModal();
        await fetchProjects(selectedProject.value ? pagination.value.currentPage : 1);
    } catch (err: any) {
        if (err.errors) {
            serverErrors.value = err.errors;
        } else {
            addToast('error', err.message || 'An error occurred while saving.');
        }
    } finally {
        isSubmitting.value = false;
    }
}

function openDeleteModal(project: Project) {
    projectToDelete.value = project;
    isDeleteModalOpen.value = true;
}

function closeDeleteModal() {
    isDeleteModalOpen.value = false;
    projectToDelete.value = null;
}

async function handleConfirmDelete() {
    if (!projectToDelete.value) return;

    isDeleting.value = true;
    try {
        await projectApi.deleteProject(projectToDelete.value.id);
        addToast('success', `Project "${projectToDelete.value.project_name}" deleted.`);
        closeDeleteModal();
        const nextPageToFetch = filteredProjects.value.length === 1 && pagination.value.currentPage > 1
            ? pagination.value.currentPage - 1
            : pagination.value.currentPage;
        await fetchProjects(nextPageToFetch);
    } catch (err: any) {
        addToast('error', err.message || 'Failed to delete project.');
    } finally {
        isDeleting.value = false;
    }
}

onMounted(() => {
    fetchProjects();
});
</script>

<template>
    <Head title="Client Project Tracker" />

    <div class="flex flex-col gap-6 p-6 max-w-7xl mx-auto w-full">
        <!-- Header Banner -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-sidebar-border/70 dark:border-sidebar-border pb-5">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <div class="rounded-lg bg-primary p-2 text-primary-content">
                        <Layers class="h-5 w-5" />
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">Client Project Tracker</h1>
                </div>
                <p class="text-sm text-muted-foreground">
                    Manage agency client projects, monitor timelines, priority levels, and deliverables.
                </p>
            </div>

            <button
                @click="openCreateModal"
                class="btn btn-info btn-sm gap-2 shadow-sm font-medium px-4"
            >
                <Plus class="h-4 w-4" />
                <span>New Project</span>
            </button>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <!-- Total -->
            <div class="card bg-card border border-sidebar-border/70 dark:border-sidebar-border p-4 shadow-xs flex flex-row items-center gap-3 rounded-xl">
                <div class="rounded-lg bg-primary/10 p-2.5 text-primary shrink-0">
                    <Briefcase class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs text-muted-foreground font-medium">Total</div>
                    <div class="text-xl font-extrabold text-foreground">{{ stats.total }}</div>
                </div>
            </div>

            <!-- Planning -->
            <div class="card bg-card border border-sidebar-border/70 dark:border-sidebar-border p-4 shadow-xs flex flex-row items-center gap-3 rounded-xl">
                <div class="rounded-lg bg-info/15 p-2.5 text-info shrink-0">
                    <ListTodo class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs text-muted-foreground font-medium">Planning</div>
                    <div class="text-xl font-extrabold text-info">{{ stats.planning }}</div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="card bg-card border border-sidebar-border/70 dark:border-sidebar-border p-4 shadow-xs flex flex-row items-center gap-3 rounded-xl">
                <div class="rounded-lg bg-warning/15 p-2.5 text-amber-500 dark:text-amber-400 shrink-0">
                    <Clock class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs text-muted-foreground font-medium">In Progress</div>
                    <div class="text-xl font-extrabold text-amber-500 dark:text-amber-400">{{ stats.inProgress }}</div>
                </div>
            </div>

            <!-- On Hold -->
            <div class="card bg-card border border-sidebar-border/70 dark:border-sidebar-border p-4 shadow-xs flex flex-row items-center gap-3 rounded-xl">
                <div class="rounded-lg bg-neutral/15 p-2.5 text-slate-600 dark:text-slate-300 shrink-0">
                    <PauseCircle class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs text-muted-foreground font-medium">On Hold</div>
                    <div class="text-xl font-extrabold text-slate-700 dark:text-slate-200">{{ stats.onHold }}</div>
                </div>
            </div>

            <!-- Completed -->
            <div class="card bg-card border border-sidebar-border/70 dark:border-sidebar-border p-4 shadow-xs flex flex-row items-center gap-3 rounded-xl col-span-2 sm:col-span-1">
                <div class="rounded-lg bg-success/15 p-2.5 text-success shrink-0">
                    <CheckCircle2 class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs text-muted-foreground font-medium">Completed</div>
                    <div class="text-xl font-extrabold text-success">{{ stats.completed }}</div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <ProjectFiltersComponent
            :filters="filters"
            :statuses="statuses"
            :priorities="priorities"
            :total-count="stats.total"
            :filtered-count="pagination.total"
            :view-mode="viewMode"
            @update:filters="handleFilterUpdate"
            @update:view-mode="viewMode = $event"
            @reset="handleFilterReset"
        />

        <!-- Projects Listing -->
        <div v-if="isLoading">
            <SkeletonLoader :view-mode="viewMode" />
        </div>

        <div v-else-if="filteredProjects.length === 0">
            <EmptyState
                :has-filters="hasActiveFilters"
                @clear-filters="handleFilterReset"
                @create-project="openCreateModal"
            />
        </div>

        <div v-else>
            <ProjectTable
                v-if="viewMode === 'table'"
                :projects="filteredProjects"
                @edit="openEditModal"
                @delete="openDeleteModal"
            />
            <ProjectCardGrid
                v-else
                :projects="filteredProjects"
                @edit="openEditModal"
                @delete="openDeleteModal"
            />

            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :per-page="pagination.perPage"
                :total="pagination.total"
                :from="pagination.from"
                :to="pagination.to"
                @update:page="fetchProjects"
            />
        </div>

        <!-- Modals & Toasts -->
        <ProjectFormModal
            :is-open="isFormModalOpen"
            :project="selectedProject"
            :statuses="statuses"
            :priorities="priorities"
            :is-submitting="isSubmitting"
            :server-errors="serverErrors"
            @close="closeFormModal"
            @submit="handleFormSubmit"
        />

        <ConfirmDeleteModal
            :is-open="isDeleteModalOpen"
            :project="projectToDelete"
            :is-deleting="isDeleting"
            @close="closeDeleteModal"
            @confirm="handleConfirmDelete"
        />

        <ToastNotification :toasts="toasts" @dismiss="dismissToast" />
    </div>
</template>
