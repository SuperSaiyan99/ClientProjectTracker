<script setup lang="ts">
import type { ProjectPriority } from '@/types/project';
import { computed } from 'vue';

const props = defineProps<{
    priority: ProjectPriority;
    size?: 'sm' | 'md' | 'lg';
}>();

const badgeClass = computed(() => {
    switch (props.priority) {
        case 'High':
            return 'bg-destructive/10 text-destructive border-destructive/40 font-semibold';
        case 'Medium':
            return 'bg-warning/10 text-amber-600 dark:text-warning border-warning/40';
        case 'Low':
            return 'bg-muted text-muted-foreground border-sidebar-border';
        default:
            return 'badge-ghost';
    }
});
</script>

<template>
    <span
        class="badge font-medium border shadow-2xs"
        :class="[badgeClass, size === 'sm' ? 'badge-sm py-2 px-2.5 text-xs' : size === 'lg' ? 'badge-lg py-3 px-4' : 'badge-md py-2.5 px-3']"
    >
        {{ priority }} Priority
    </span>
</template>
