<script setup lang="ts">
import { FolderSearch, Plus } from '@lucide/vue';

defineProps<{
    hasFilters: boolean;
}>();

const emit = defineEmits<{
    (e: 'clearFilters'): void;
    (e: 'createProject'): void;
}>();
</script>

<template>
    <div class="card bg-card p-8 text-center shadow-2xs border border-sidebar-border/70 dark:border-sidebar-border my-4 rounded-xl">
        <div class="card-body items-center text-center max-w-md mx-auto p-0">
            <div class="rounded-full bg-muted p-4 mb-2 text-muted-foreground">
                <FolderSearch class="h-8 w-8" />
            </div>
            <h3 class="card-title text-lg font-semibold text-foreground">
                {{ hasFilters ? 'No matching projects found' : 'No projects recorded yet' }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{
                    hasFilters
                        ? 'Try adjusting your search query, status, or priority filters to find what you are looking for.'
                        : 'Get started by creating your first client project tracker entry.'
                }}
            </p>
            <div class="card-actions justify-center mt-4 gap-2">
                <button
                    v-if="hasFilters"
                    @click="emit('clearFilters')"
                    class="btn btn-outline btn-sm text-foreground hover:bg-muted"
                >
                    Reset Filters
                </button>
                <button
                    @click="emit('createProject')"
                    class="btn btn-primary btn-sm gap-1.5"
                >
                    <Plus class="h-4 w-4" />
                    New Project
                </button>
            </div>
        </div>
    </div>
</template>
