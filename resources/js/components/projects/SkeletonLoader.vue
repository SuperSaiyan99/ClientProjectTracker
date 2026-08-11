<script setup lang="ts">
defineProps<{
    viewMode?: 'table' | 'grid';
}>();
</script>

<template>
    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 6" :key="i" class="card bg-card p-5 shadow-2xs border border-sidebar-border/70 dark:border-sidebar-border space-y-3.5 rounded-xl">
            <div class="flex items-center justify-between">
                <div class="h-4 w-28 bg-muted animate-pulse rounded-md"></div>
                <div class="h-5 w-20 bg-muted animate-pulse rounded-full"></div>
            </div>
            <div class="h-6 w-3/4 bg-muted animate-pulse rounded-md"></div>
            <div class="h-10 w-full bg-muted animate-pulse rounded-md"></div>
            <div class="flex items-center justify-between pt-3 border-t border-sidebar-border/60 dark:border-sidebar-border">
                <div class="h-4 w-24 bg-muted animate-pulse rounded-md"></div>
                <div class="h-4 w-24 bg-muted animate-pulse rounded-md"></div>
            </div>
        </div>
    </div>
    <div v-else class="space-y-3">
        <div class="card bg-card p-4 shadow-2xs border border-sidebar-border/70 dark:border-sidebar-border rounded-xl">
            <div class="space-y-3">
                <div v-for="i in 5" :key="i" class="flex items-center justify-between gap-4 py-2 border-b border-sidebar-border/40 last:border-0">
                    <div class="h-4 w-1/5 bg-muted animate-pulse rounded-md"></div>
                    <div class="h-4 w-1/4 bg-muted animate-pulse rounded-md"></div>
                    <div class="h-6 w-20 bg-muted animate-pulse rounded-full"></div>
                    <div class="h-6 w-16 bg-muted animate-pulse rounded-full"></div>
                    <div class="h-4 w-24 bg-muted animate-pulse rounded-md"></div>
                    <div class="h-8 w-16 bg-muted animate-pulse rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>
</template>
