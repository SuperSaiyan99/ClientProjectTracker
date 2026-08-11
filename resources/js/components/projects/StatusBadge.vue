<script setup lang="ts">
import type { ProjectStatus } from '@/types/project';
import { computed } from 'vue';

const props = defineProps<{
    status: ProjectStatus;
    size?: 'sm' | 'md' | 'lg';
}>();

const badgeClass = computed(() => {
    switch (props.status) {
        case 'Planning':
            return 'bg-info/15 text-info border-info/30';
        case 'In Progress':
            return 'bg-warning/15 text-amber-600 dark:text-warning border-warning/30';
        case 'On Hold':
            return 'bg-neutral/15 text-neutral-700 dark:text-neutral-300 border-neutral/30';
        case 'Completed':
            return 'bg-success/15 text-emerald-600 dark:text-success border-success/30';
        default:
            return 'badge-ghost';
    }
});

const dotClass = computed(() => {
    switch (props.status) {
        case 'Planning':
            return 'bg-info animate-pulse';
        case 'In Progress':
            return 'bg-warning animate-pulse';
        case 'On Hold':
            return 'bg-neutral-400';
        case 'Completed':
            return 'bg-success';
        default:
            return 'bg-muted-foreground';
    }
});
</script>

<template>
    <span
        class="badge gap-1.5 font-medium tracking-wide border shadow-2xs"
        :class="[badgeClass, size === 'sm' ? 'badge-sm py-2 px-2.5 text-xs' : size === 'lg' ? 'badge-lg py-3 px-4' : 'badge-md py-2.5 px-3']"
    >
        <span class="h-1.5 w-1.5 rounded-full shrink-0" :class="dotClass"></span>
        {{ status }}
    </span>
</template>
