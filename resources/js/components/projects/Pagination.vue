<script setup lang="ts">
import { computed } from 'vue';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

const props = defineProps<{
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
    from?: number;
    to?: number;
}>();

const emit = defineEmits<{
    (e: 'update:page', page: number): void;
}>();

const pages = computed(() => {
    const current = props.currentPage;
    const last = props.lastPage;
    const delta = 1;
    const range: number[] = [];

    for (let i = 1; i <= last; i++) {
        if (
            i === 1 ||
            i === last ||
            (i >= current - delta && i <= current + delta)
        ) {
            range.push(i);
        }
    }

    const pagesWithEllipsis: (number | string)[] = [];
    let l: number | undefined;

    for (const i of range) {
        if (l !== undefined) {
            if (i - l === 2) {
                pagesWithEllipsis.push(l + 1);
            } else if (i - l > 2) {
                pagesWithEllipsis.push('...');
            }
        }
        pagesWithEllipsis.push(i);
        l = i;
    }

    return pagesWithEllipsis;
});

function changePage(page: number | string) {
    if (typeof page === 'number' && page !== props.currentPage && page >= 1 && page <= props.lastPage) {
        emit('update:page', page);
    }
}
</script>

<template>
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6 px-1">
        <!-- Range Information -->
        <div class="text-xs text-muted-foreground font-medium order-2 sm:order-1">
            <span v-if="total > 0">
                Showing <span class="text-foreground font-semibold">{{ from || 0 }}</span> to
                <span class="text-foreground font-semibold">{{ to || 0 }}</span> of
                <span class="text-foreground font-semibold">{{ total }}</span> entries
            </span>
            <span v-else>
                Showing 0 entries
            </span>
        </div>

        <!-- Pagination Controls -->
        <div v-if="lastPage > 1" class="flex items-center gap-1.5 order-1 sm:order-2">
            <!-- Prev Button -->
            <button
                @click="changePage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="btn btn-outline btn-xs btn-square hover:bg-primary/10 hover:text-primary border-sidebar-border dark:border-sidebar-border text-muted-foreground disabled:opacity-50 disabled:pointer-events-none"
                aria-label="Previous Page"
            >
                <ChevronLeft class="h-4 w-4" />
            </button>

            <!-- Page Buttons -->
            <template v-for="(page, idx) in pages" :key="idx">
                <span
                    v-if="page === '...'"
                    class="px-2 text-muted-foreground text-xs font-semibold select-none flex items-end justify-center h-7"
                >
                    &hellip;
                </span>
                <button
                    v-else
                    @click="changePage(page)"
                    class="btn btn-xs min-w-[28px] h-7 font-semibold"
                    :class="[
                        page === currentPage
                            ? 'btn-primary text-primary-content hover:btn-primary'
                            : 'btn-outline border-sidebar-border dark:border-sidebar-border text-muted-foreground hover:bg-primary/10 hover:text-primary hover:border-primary/20'
                    ]"
                >
                    {{ page }}
                </button>
            </template>

            <!-- Next Button -->
            <button
                @click="changePage(currentPage + 1)"
                :disabled="currentPage === lastPage"
                class="btn btn-outline btn-xs btn-square hover:bg-primary/10 hover:text-primary border-sidebar-border dark:border-sidebar-border text-muted-foreground disabled:opacity-50 disabled:pointer-events-none"
                aria-label="Next Page"
            >
                <ChevronRight class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
