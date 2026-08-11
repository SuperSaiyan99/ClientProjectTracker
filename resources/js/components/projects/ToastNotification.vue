<script setup lang="ts">
import { CheckCircle2, AlertCircle, X } from '@lucide/vue';

export interface ToastItem {
    id: number;
    type: 'success' | 'error' | 'info';
    message: string;
}

defineProps<{
    toasts: ToastItem[];
}>();

const emit = defineEmits<{
    (e: 'dismiss', id: number): void;
}>();
</script>

<template>
    <div class="toast toast-end toast-bottom z-50 p-4 space-y-2">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="alert shadow-md border flex items-center justify-between gap-3 text-sm py-3 px-4 min-w-[280px]"
                :class="
                    toast.type === 'success'
                        ? 'alert-success text-success-content'
                        : toast.type === 'error'
                        ? 'alert-error text-error-content'
                        : 'alert-info text-info-content'
                "
            >
                <div class="flex items-center gap-2">
                    <CheckCircle2 v-if="toast.type === 'success'" class="h-4 w-4 shrink-0" />
                    <AlertCircle v-else-if="toast.type === 'error'" class="h-4 w-4 shrink-0" />
                    <span>{{ toast.message }}</span>
                </div>
                <button
                    @click="emit('dismiss', toast.id)"
                    class="btn btn-ghost btn-xs btn-square opacity-70 hover:opacity-100"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateY(20px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(100px);
}
</style>
