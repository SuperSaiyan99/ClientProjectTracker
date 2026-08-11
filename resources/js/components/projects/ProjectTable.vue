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
    <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card shadow-xs">
        <table class="table w-full text-sm">
            <thead>
                <tr class="bg-muted/50 text-muted-foreground font-semibold text-xs uppercase tracking-wider border-b border-sidebar-border/70 dark:border-sidebar-border">
                    <th class="py-3.5 px-4">Client Name</th>
                    <th class="py-3.5 px-4">Project Name</th>
                    <th class="py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4">Priority</th>
                    <th class="py-3.5 px-4">Timeline</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-sidebar-border/60 dark:divide-sidebar-border">
                <tr
                    v-for="project in projects"
                    :key="project.id"
                    class="hover:bg-muted/40 transition-colors group"
                >
                    <!-- Client Name -->
                    <td class="py-3.5 px-4 font-medium text-foreground">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary font-bold text-xs uppercase">
                                {{ project.client_name.substring(0, 2) }}
                            </div>
                            <span class="font-medium text-sm text-foreground">{{ project.client_name }}</span>
                        </div>
                    </td>

                    <!-- Project Name & Description -->
                    <td class="py-3.5 px-4">
                        <div class="max-w-xs md:max-w-md">
                            <div class="font-semibold text-sm text-foreground group-hover:text-primary transition-colors">
                                {{ project.project_name }}
                            </div>
                            <p v-if="project.description" class="text-xs text-muted-foreground line-clamp-1 mt-0.5">
                                {{ project.description }}
                            </p>
                        </div>
                    </td>

                    <!-- Status Badge -->
                    <td class="py-3.5 px-4 whitespace-nowrap">
                        <StatusBadge :status="project.status" size="sm" />
                    </td>

                    <!-- Priority Badge -->
                    <td class="py-3.5 px-4 whitespace-nowrap">
                        <PriorityBadge :priority="project.priority" size="sm" />
                    </td>

                    <!-- Timeline -->
                    <td class="py-3.5 px-4 text-xs whitespace-nowrap">
                        <div class="space-y-1">
                            <div class="flex items-center gap-1.5 text-foreground/80 font-medium">
                                <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                                <span>{{ formatDate(project.start_date) }} &rarr; {{ formatDate(project.due_date) }}</span>
                            </div>
                            <div
                                v-if="project.status !== 'Completed'"
                                class="flex items-center gap-1 text-[11px]"
                                :class="getDaysRemaining(project.due_date).isOverdue ? 'text-destructive font-medium' : 'text-muted-foreground'"
                            >
                                <AlertCircle v-if="getDaysRemaining(project.due_date).isOverdue" class="h-3 w-3" />
                                <Clock v-else class="h-3 w-3" />
                                <span>{{ getDaysRemaining(project.due_date).text }}</span>
                            </div>
                        </div>
                    </td>

                    <!-- Action Buttons -->
                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1">
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
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
