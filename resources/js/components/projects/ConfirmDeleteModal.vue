<script setup lang="ts">
import type { Project } from '@/types/project';
import { AlertTriangle, Trash2 } from '@lucide/vue';

defineProps<{
    isOpen: boolean;
    project: Project | null;
    isDeleting: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();
</script>

<template>
    <dialog v-if="isOpen" class="modal modal-open bg-black/60 z-50 transition-none animate-none">
        <div class="modal-box max-w-md border border-sidebar-border bg-card text-card-foreground shadow-2xl rounded-xl transition-none animate-none transform-none">
            <div class="flex items-start gap-4">
                <div class="rounded-full bg-destructive/10 p-3 text-destructive shrink-0">
                    <AlertTriangle class="h-6 w-6" />
                </div>
                <div>
                    <h3 class="font-bold text-lg text-foreground">Delete Project</h3>
                    <p class="py-2 text-sm text-muted-foreground leading-relaxed">
                        Are you sure you want to delete
                        <span class="font-semibold text-foreground">{{ project?.project_name }}</span>
                        for
                        <span class="font-semibold text-foreground">{{ project?.client_name }}</span>?
                        This action cannot be undone.
                    </p>
                </div>
            </div>
            <div class="modal-action gap-2 pt-2">
                <button
                    type="button"
                    class="btn btn-ghost btn-sm text-muted-foreground hover:text-foreground"
                    :disabled="isDeleting"
                    @click="emit('close')"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    class="btn btn-error btn-sm text-white gap-1.5"
                    :disabled="isDeleting"
                    @click="emit('confirm')"
                >
                    <span v-if="isDeleting" class="loading loading-spinner loading-xs"></span>
                    <Trash2 v-else class="h-4 w-4" />
                    Delete Project
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop" @click="emit('close')">
            <button>close</button>
        </form>
    </dialog>
</template>
