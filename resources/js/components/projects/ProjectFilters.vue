<script setup lang="ts">
import type {
    ProjectStatus,
    ProjectPriority,
    ProjectFilters,
} from '@/types/project';
import {
    Search,
    X,
    Filter,
    Flag,
    ArrowUpDown,
    RotateCcw,
    LayoutGrid,
    LayoutList,
    Loader2,
} from '@lucide/vue';
import { computed, ref, watch, onUnmounted } from 'vue';

const props = defineProps<{
    filters: ProjectFilters;
    statuses: ProjectStatus[];
    priorities: ProjectPriority[];
    totalCount: number;
    filteredCount: number;
    viewMode: 'table' | 'grid';
}>();

const emit = defineEmits<{
    (e: 'update:filters', filters: ProjectFilters): void;
    (e: 'update:viewMode', mode: 'table' | 'grid'): void;
    (e: 'reset'): void;
}>();

const searchInput = ref<string>(props.filters.search || '');
const isDebouncing = ref<boolean>(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(
    () => props.filters.search,
    (newVal) => {
        if (newVal !== searchInput.value) {
            searchInput.value = newVal || '';
            isDebouncing.value = false;
        }
    },
);

function handleSearchInput(val: string) {
    searchInput.value = val;
    isDebouncing.value = true;

    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        isDebouncing.value = false;
        emit('update:filters', { ...props.filters, search: val });
    }, 1500);
}

function clearSearch() {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    isDebouncing.value = false;
    searchInput.value = '';
    emit('update:filters', { ...props.filters, search: '' });
}

const hasActiveFilters = computed(() => {
    return !!(
        props.filters.search ||
        searchInput.value ||
        props.filters.status ||
        props.filters.priority ||
        (props.filters.sort && props.filters.sort !== 'created_at')
    );
});

function updateStatus(val: string) {
    emit('update:filters', {
        ...props.filters,
        search: searchInput.value,
        status: val,
    });
}

function updatePriority(val: string) {
    emit('update:filters', {
        ...props.filters,
        search: searchInput.value,
        priority: val,
    });
}

function updateSort(val: string) {
    let sort = val;
    let direction: 'asc' | 'desc' = 'asc';
    if (val.startsWith('-')) {
        sort = val.substring(1);
        direction = 'desc';
    }
    emit('update:filters', {
        ...props.filters,
        search: searchInput.value,
        sort,
        direction,
    });
}

const currentSortValue = computed(() => {
    if (!props.filters.sort) return '-created_at';
    return props.filters.direction === 'desc'
        ? `-${props.filters.sort}`
        : props.filters.sort;
});

onUnmounted(() => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
});
</script>

<template>
    <div
        class="mb-6 flex flex-col justify-between gap-3.5 border-b border-sidebar-border/70 pb-4 md:flex-row md:items-center dark:border-sidebar-border"
    >
        <!-- Horizontal Toolbar: Search + Icon Dropdowns + Reset -->
        <div class="flex flex-1 flex-wrap items-center gap-3 sm:gap-3.5">
            <!-- Search Input (Shorter Width) -->
            <div class="relative w-full sm:w-52 md:w-56 shrink-0">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <input
                    :value="searchInput"
                    @input="
                        handleSearchInput(
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                    type="text"
                    placeholder="Search client, project..."
                    class="input-bordered input h-8 w-full rounded-lg border-sidebar-border bg-background pr-10 pl-9 text-xs text-foreground input-sm focus:input-primary"
                />

                <div
                    class="absolute top-1/2 right-2 flex -translate-y-1/2 items-center gap-1"
                >
                    <span
                        v-if="isDebouncing"
                        class="flex items-center gap-1 rounded bg-muted px-1 py-0.5 font-mono text-[10px] text-muted-foreground"
                    >
                        <Loader2 class="h-3 w-3 animate-spin text-primary" />
                    </span>
                    <button
                        v-if="searchInput"
                        @click="clearSearch"
                        class="btn btn-square btn-ghost text-muted-foreground btn-xs hover:text-foreground"
                        title="Clear search"
                    >
                        <X class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <!-- Status Icon Dropdown -->
            <div class="relative flex shrink-0 items-center">
                <Filter
                    class="pointer-events-none absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
                />
                <select
                    :value="filters.status || ''"
                    @change="
                        updateStatus(($event.target as HTMLSelectElement).value)
                    "
                    class="select-bordered select h-8 min-w-[125px] shrink-0 cursor-pointer rounded-lg border-sidebar-border bg-background pr-8 pl-8 text-xs text-foreground select-xs focus:select-primary"
                >
                    <option value="" class="bg-background text-foreground">
                        Status: All
                    </option>
                    <option
                        v-for="s in statuses"
                        :key="s"
                        :value="s"
                        class="bg-background text-foreground"
                    >
                        {{ s }}
                    </option>
                </select>
            </div>

            <!-- Priority Icon Dropdown -->
            <div class="relative flex shrink-0 items-center">
                <Flag
                    class="pointer-events-none absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
                />
                <select
                    :value="filters.priority || ''"
                    @change="
                        updatePriority(
                            ($event.target as HTMLSelectElement).value,
                        )
                    "
                    class="select-bordered select h-8 min-w-[130px] shrink-0 cursor-pointer rounded-lg border-sidebar-border bg-background pr-8 pl-8 text-xs text-foreground select-xs focus:select-primary"
                >
                    <option value="" class="bg-background text-foreground">
                        Priority: All
                    </option>
                    <option
                        v-for="p in priorities"
                        :key="p"
                        :value="p"
                        class="bg-background text-foreground"
                    >
                        {{ p }} Priority
                    </option>
                </select>
            </div>

            <!-- Sort Icon Dropdown -->
            <div class="relative flex shrink-0 items-center">
                <ArrowUpDown
                    class="pointer-events-none absolute top-1/2 left-2.5 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"
                />
                <select
                    :value="currentSortValue"
                    @change="
                        updateSort(($event.target as HTMLSelectElement).value)
                    "
                    class="select-bordered select h-8 min-w-[145px] shrink-0 cursor-pointer rounded-lg border-sidebar-border bg-background pr-8 pl-8 text-xs text-foreground select-xs focus:select-primary"
                >
                    <option
                        value="-created_at"
                        class="bg-background text-foreground"
                    >
                        Sort: Newest
                    </option>
                    <option
                        value="created_at"
                        class="bg-background text-foreground"
                    >
                        Sort: Oldest
                    </option>
                    <option
                        value="due_date"
                        class="bg-background text-foreground"
                    >
                        Due: Earliest
                    </option>
                    <option
                        value="-due_date"
                        class="bg-background text-foreground"
                    >
                        Due: Latest
                    </option>
                    <option
                        value="start_date"
                        class="bg-background text-foreground"
                    >
                        Start: Earliest
                    </option>
                    <option
                        value="client_name"
                        class="bg-background text-foreground"
                    >
                        Client A-Z
                    </option>
                    <option
                        value="project_name"
                        class="bg-background text-foreground"
                    >
                        Project A-Z
                    </option>
                </select>
            </div>

            <!-- Reset Button -->
            <button
                v-if="hasActiveFilters"
                @click="emit('reset')"
                class="btn h-8 shrink-0 gap-1 btn-ghost px-2 text-destructive btn-xs hover:bg-destructive/10"
                title="Clear all active filters"
            >
                <RotateCcw class="h-3 w-3" />
                <span>Reset</span>
            </button>
        </div>

        <!-- Right Side: View Toggle & Counters -->
        <div
            class="flex shrink-0 items-center justify-between gap-3 text-xs text-muted-foreground md:justify-end"
        >
            <div class="flex items-center gap-1.5 font-medium">
                <span>
                    Showing
                    <strong class="font-semibold text-foreground">{{
                        filteredCount
                    }}</strong>
                    of
                    <strong class="font-semibold text-foreground">{{
                        totalCount
                    }}</strong>
                </span>
            </div>

            <!-- View Toggle -->
            <div
                class="join rounded-lg border border-sidebar-border bg-background"
            >
                <button
                    @click="emit('update:viewMode', 'table')"
                    class="btn join-item h-8 px-2.5 btn-xs"
                    :class="
                        viewMode === 'table'
                            ? 'btn-neutral'
                            : 'btn-ghost text-muted-foreground hover:text-foreground'
                    "
                    title="Table View"
                >
                    <LayoutList class="h-3.5 w-3.5" />
                </button>
                <button
                    @click="emit('update:viewMode', 'grid')"
                    class="btn join-item h-8 px-2.5 btn-xs"
                    :class="
                        viewMode === 'grid'
                            ? 'btn-neutral'
                            : 'btn-ghost text-muted-foreground hover:text-foreground'
                    "
                    title="Grid View"
                >
                    <LayoutGrid class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
    </div>
</template>
