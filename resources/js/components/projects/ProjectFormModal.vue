<script setup lang="ts">
import type { Project, ProjectFormData, ProjectStatus, ProjectPriority } from '@/types/project';
import { ref, watch, computed } from 'vue';
import { X, Calendar, Building2, Folder, FileText } from '@lucide/vue';

const props = defineProps<{
    isOpen: boolean;
    project: Project | null;
    statuses: ProjectStatus[];
    priorities: ProjectPriority[];
    isSubmitting: boolean;
    serverErrors: Record<string, string[]>;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'submit', formData: ProjectFormData): void;
}>();

const form = ref<ProjectFormData>({
    client_name: '',
    project_name: '',
    description: '',
    status: 'Planning',
    priority: 'Medium',
    start_date: new Date().toISOString().split('T')[0],
    due_date: new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0],
});

const clientErrors = ref<Record<string, string>>({});

const isEditing = computed(() => !!props.project);

watch(
    () => props.isOpen,
    (newVal) => {
        if (newVal) {
            clientErrors.value = {};
            if (props.project) {
                form.value = {
                    client_name: props.project.client_name,
                    project_name: props.project.project_name,
                    description: props.project.description || '',
                    status: props.project.status,
                    priority: props.project.priority,
                    start_date: props.project.start_date,
                    due_date: props.project.due_date,
                };
            } else {
                const today = new Date().toISOString().split('T')[0];
                const defaultDue = new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0];
                form.value = {
                    client_name: '',
                    project_name: '',
                    description: '',
                    status: props.statuses[0] || 'Planning',
                    priority: props.priorities[1] || 'Medium',
                    start_date: today,
                    due_date: defaultDue,
                };
            }
        }
    },
    { immediate: true }
);

function validateClientSide(): boolean {
    clientErrors.value = {};

    if (!form.value.client_name.trim()) {
        clientErrors.value.client_name = 'Client name is required.';
    } else if (form.value.client_name.length > 255) {
        clientErrors.value.client_name = 'Client name cannot exceed 255 characters.';
    }

    if (!form.value.project_name.trim()) {
        clientErrors.value.project_name = 'Project name is required.';
    } else if (form.value.project_name.length > 255) {
        clientErrors.value.project_name = 'Project name cannot exceed 255 characters.';
    }

    if (!form.value.status) {
        clientErrors.value.status = 'Status is required.';
    }

    if (!form.value.priority) {
        clientErrors.value.priority = 'Priority is required.';
    }

    if (!form.value.start_date) {
        clientErrors.value.start_date = 'Start date is required.';
    }

    if (!form.value.due_date) {
        clientErrors.value.due_date = 'Due date is required.';
    } else if (form.value.start_date && form.value.due_date < form.value.start_date) {
        clientErrors.value.due_date = 'The due date must be a date after or equal to start date.';
    }

    return Object.keys(clientErrors.value).length === 0;
}

function handleSubmit() {
    if (!validateClientSide()) {
        return;
    }
    emit('submit', { ...form.value });
}

function getFieldError(fieldName: string): string | null {
    if (clientErrors.value[fieldName]) {
        return clientErrors.value[fieldName];
    }
    if (props.serverErrors && props.serverErrors[fieldName] && props.serverErrors[fieldName].length > 0) {
        return props.serverErrors[fieldName][0];
    }
    return null;
}
</script>

<template>
    <dialog v-if="isOpen" class="modal modal-open bg-black/60 z-50 transition-none animate-none">
        <div class="modal-box max-w-xl border border-sidebar-border bg-card text-card-foreground shadow-2xl p-0 overflow-hidden rounded-xl transition-none animate-none transform-none">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-sidebar-border bg-card">
                <div class="flex items-center gap-2.5">
                    <div class="rounded-lg bg-primary/10 p-2 text-primary">
                        <Folder class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-foreground">
                            {{ isEditing ? 'Edit Project' : 'Create New Project' }}
                        </h3>
                        <p class="text-xs text-muted-foreground">
                            {{ isEditing ? 'Update project parameters and schedule' : 'Add a new client project to track' }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="btn btn-ghost btn-xs btn-square text-muted-foreground hover:text-foreground"
                    @click="emit('close')"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Modal Form Body -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                <!-- Client & Project Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label pb-1">
                            <span class="label-text font-medium text-xs text-foreground flex items-center gap-1.5">
                                <Building2 class="h-3.5 w-3.5 text-muted-foreground" />
                                Client Name <span class="text-destructive">*</span>
                            </span>
                        </label>
                        <input
                            v-model="form.client_name"
                            type="text"
                            placeholder="e.g. Acme Corporation"
                            class="input input-bordered input-sm w-full bg-background text-foreground border-sidebar-border focus:border-primary"
                            :class="{ 'input-error': getFieldError('client_name') }"
                        />
                        <p v-if="getFieldError('client_name')" class="text-destructive text-xs mt-1">
                            {{ getFieldError('client_name') }}
                        </p>
                    </div>

                    <div class="form-control">
                        <label class="label pb-1">
                            <span class="label-text font-medium text-xs text-foreground flex items-center gap-1.5">
                                <Folder class="h-3.5 w-3.5 text-muted-foreground" />
                                Project Name <span class="text-destructive">*</span>
                            </span>
                        </label>
                        <input
                            v-model="form.project_name"
                            type="text"
                            placeholder="e.g. Website Redesign"
                            class="input input-bordered input-sm w-full bg-background text-foreground border-sidebar-border focus:border-primary"
                            :class="{ 'input-error': getFieldError('project_name') }"
                        />
                        <p v-if="getFieldError('project_name')" class="text-destructive text-xs mt-1">
                            {{ getFieldError('project_name') }}
                        </p>
                    </div>
                </div>

                <!-- Status & Priority -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label pb-1">
                            <span class="label-text font-medium text-xs text-foreground">
                                Status <span class="text-destructive">*</span>
                            </span>
                        </label>
                        <select
                            v-model="form.status"
                            class="select select-bordered select-sm w-full bg-background text-foreground border-sidebar-border focus:border-primary"
                            :class="{ 'select-error': getFieldError('status') }"
                        >
                            <option v-for="s in statuses" :key="s" :value="s">
                                {{ s }}
                            </option>
                        </select>
                        <p v-if="getFieldError('status')" class="text-destructive text-xs mt-1">
                            {{ getFieldError('status') }}
                        </p>
                    </div>

                    <div class="form-control">
                        <label class="label pb-1">
                            <span class="label-text font-medium text-xs text-foreground">
                                Priority <span class="text-destructive">*</span>
                            </span>
                        </label>
                        <select
                            v-model="form.priority"
                            class="select select-bordered select-sm w-full bg-background text-foreground border-sidebar-border focus:border-primary"
                            :class="{ 'select-error': getFieldError('priority') }"
                        >
                            <option v-for="p in priorities" :key="p" :value="p">
                                {{ p }} Priority
                            </option>
                        </select>
                        <p v-if="getFieldError('priority')" class="text-destructive text-xs mt-1">
                            {{ getFieldError('priority') }}
                        </p>
                    </div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label pb-1">
                            <span class="label-text font-medium text-xs text-foreground flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                                Start Date <span class="text-destructive">*</span>
                            </span>
                        </label>
                        <input
                            v-model="form.start_date"
                            type="date"
                            class="input input-bordered input-sm w-full bg-background text-foreground border-sidebar-border focus:border-primary"
                            :class="{ 'input-error': getFieldError('start_date') }"
                        />
                        <p v-if="getFieldError('start_date')" class="text-destructive text-xs mt-1">
                            {{ getFieldError('start_date') }}
                        </p>
                    </div>

                    <div class="form-control">
                        <label class="label pb-1">
                            <span class="label-text font-medium text-xs text-foreground flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                                Due Date <span class="text-destructive">*</span>
                            </span>
                        </label>
                        <input
                            v-model="form.due_date"
                            type="date"
                            class="input input-bordered input-sm w-full bg-background text-foreground border-sidebar-border focus:border-primary"
                            :class="{ 'input-error': getFieldError('due_date') }"
                        />
                        <p v-if="getFieldError('due_date')" class="text-destructive text-xs mt-1">
                            {{ getFieldError('due_date') }}
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-control">
                    <label class="label pb-1">
                        <span class="label-text font-medium text-xs text-foreground flex items-center gap-1.5">
                            <FileText class="h-3.5 w-3.5 text-muted-foreground" />
                            Description (Optional)
                        </span>
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Brief project details or scope outline..."
                        class="textarea textarea-bordered textarea-sm w-full bg-background text-foreground border-sidebar-border focus:textarea-primary text-sm"
                        :class="{ 'textarea-error': getFieldError('description') }"
                    ></textarea>
                    <p v-if="getFieldError('description')" class="text-destructive text-xs mt-1">
                        {{ getFieldError('description') }}
                    </p>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-sidebar-border mt-6">
                    <button
                        type="button"
                        class="btn btn-ghost btn-sm text-muted-foreground hover:text-foreground"
                        :disabled="isSubmitting"
                        @click="emit('close')"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-info btn-sm px-5"
                        :disabled="isSubmitting"
                    >
                        <span v-if="isSubmitting" class="loading loading-spinner loading-xs"></span>
                        {{ isEditing ? 'Save Changes' : 'Create Project' }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop" @click="emit('close')">
            <button>close</button>
        </form>
    </dialog>
</template>
