<script setup lang="ts">
import type { Project } from '@/types/project';
import StatusBadge from './StatusBadge.vue';
import PriorityBadge from './PriorityBadge.vue';
import { Pencil, Trash2, Calendar, Building2, Clock, AlertCircle } from '@lucide/vue';

defineProps<{
    projects: Project[];
}>();

const emit = defineEmits<{
    (e: 'edit', project: Project): void;
    (e: 'delete', project: Project): void;
}>();

function formatDate(dateStr: string): string {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr + 'T00:00:00');
    return new Intl.DateTimeFormat('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    }).format(date);
}

function getDaysRemaining(dueDateStr: string): { text: string; isOverdue: boolean } {
    if (!dueDateStr) return { text: '', isOverdue: false };
    const due = new Date(dueDateStr + 'T00:00:00').getTime();
    const today = new Date().setHours(0, 0, 0, 0);
    const diffDays = Math.ceil((due - today) / (1000 * 60 * 60 * 24));

    if (diffDays < 0) {
        return { text: `${Math.abs(diffDays)}d overdue`, isOverdue: true };
    } else if (diffDays === 0) {
        return { text: 'Due today', isOverdue: false };
    } else {
        return { text: `${diffDays}d left`, isOverdue: false };
    }
}
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
            v-for="project in projects"
            :key="project.id"
            class="card bg-card border border-sidebar-border/70 dark:border-sidebar-border shadow-2xs hover:shadow-md transition-all flex flex-col justify-between p-5 rounded-xl group"
        >
            <div>
                <!-- Header: Client & Actions -->
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-2 text-xs text-muted-foreground font-medium">
                        <div class="flex h-6 w-6 items-center justify-center rounded-md bg-primary/10 text-primary font-bold text-[10px] uppercase">
                            {{ project.client_name.substring(0, 2) }}
                        </div>
                        <span class="truncate max-w-[160px]">{{ project.client_name }}</span>
                    </div>

                    <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                        <button
                            @click="emit('edit', project)"
                            class="btn btn-ghost btn-xs btn-square text-muted-foreground hover:text-primary hover:bg-primary/10"
                            title="Edit project"
                        >
                            <Pencil class="h-3.5 w-3.5" />
                        </button>
                        <button
                            @click="emit('delete', project)"
                            class="btn btn-ghost btn-xs btn-square text-muted-foreground hover:text-destructive hover:bg-destructive/10"
                            title="Delete project"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <!-- Project Title -->
                <h3 class="font-bold text-base text-foreground mb-2 group-hover:text-primary transition-colors line-clamp-1">
                    {{ project.project_name }}
                </h3>

                <!-- Description -->
                <p v-if="project.description" class="text-xs text-muted-foreground line-clamp-2 mb-4 min-h-[2.5rem]">
                    {{ project.description }}
                </p>
                <p v-else class="text-xs text-muted-foreground/50 italic mb-4 min-h-[2.5rem]">
                    No description provided.
                </p>

                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <StatusBadge :status="project.status" size="sm" />
                    <PriorityBadge :priority="project.priority" size="sm" />
                </div>
            </div>

            <!-- Card Footer -->
            <div class="pt-3 border-t border-sidebar-border/60 dark:border-sidebar-border flex items-center justify-between text-xs text-muted-foreground">
                <div class="flex items-center gap-1.5 font-medium">
                    <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                    <span>Due: {{ formatDate(project.due_date) }}</span>
                </div>
                <div
                    v-if="project.status !== 'Completed'"
                    class="flex items-center gap-1 font-medium"
                    :class="getDaysRemaining(project.due_date).isOverdue ? 'text-destructive' : 'text-muted-foreground'"
                >
                    <AlertCircle v-if="getDaysRemaining(project.due_date).isOverdue" class="h-3 w-3" />
                    <Clock v-else class="h-3 w-3" />
                    <span>{{ getDaysRemaining(project.due_date).text }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
