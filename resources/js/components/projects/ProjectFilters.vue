<script setup lang="ts">
import type { ProjectStatus, ProjectPriority, ProjectFilters } from '@/types/project';
import { Search, X, Filter, Flag, ArrowUpDown, RotateCcw, LayoutGrid, LayoutList, Loader2 } from '@lucide/vue';
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
    }
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
    return !!(props.filters.search || searchInput.value || props.filters.status || props.filters.priority || (props.filters.sort && props.filters.sort !== 'created_at'));
});

function updateStatus(val: string) {
    emit('update:filters', { ...props.filters, search: searchInput.value, status: val });
}

function updatePriority(val: string) {
    emit('update:filters', { ...props.filters, search: searchInput.value, priority: val });
}

function updateSort(val: string) {
    let sort = val;
    let direction: 'asc' | 'desc' = 'asc';
    if (val.startsWith('-')) {
        sort = val.substring(1);
        direction = 'desc';
    }
    emit('update:filters', { ...props.filters, search: searchInput.value, sort, direction });
}

const currentSortValue = computed(() => {
    if (!props.filters.sort) return 'created_at';
    return props.filters.direction === 'desc' ? `-${props.filters.sort}` : props.filters.sort;
});

onUnmounted(() => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
});
</script>

<template>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-sidebar-border/70 dark:border-sidebar-border mb-6">
        <!-- Horizontal Toolbar: Search + Icon Dropdowns + Reset -->
        <div class="flex flex-wrap items-center gap-2 flex-1">
            <!-- Search Input -->
            <div class="relative flex-1 min-w-[200px] max-w-sm">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                <input
                    :value="searchInput"
                    @input="handleSearchInput(($event.target as HTMLInputElement).value)"
                    type="text"
                    placeholder="Search client, project..."
                    class="input input-bordered input-sm w-full pl-9 pr-12 focus:input-primary text-xs bg-background text-foreground border-sidebar-border h-8 rounded-lg"
                />
                
                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1">
                    <span v-if="isDebouncing" class="text-[10px] text-muted-foreground flex items-center gap-1 font-mono bg-muted px-1 py-0.5 rounded">
                        <Loader2 class="h-3 w-3 animate-spin text-primary" />
                    </span>
                    <button
                        v-if="searchInput"
                        @click="clearSearch"
                        class="btn btn-ghost btn-xs btn-square text-muted-foreground hover:text-foreground"
                        title="Clear search"
                    >
                        <X class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <!-- Status Icon Dropdown -->
            <div class="relative flex items-center shrink-0">
                <Filter class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                <select
                    :value="filters.status || ''"
                    @change="updateStatus(($event.target as HTMLSelectElement).value)"
                    class="select select-bordered select-xs h-8 pl-8 pr-6 text-xs bg-background text-foreground border-sidebar-border rounded-lg focus:select-primary cursor-pointer shrink-0 min-w-[110px]"
                >
                    <option value="" class="bg-background text-foreground">Status: All</option>
                    <option v-for="s in statuses" :key="s" :value="s" class="bg-background text-foreground">
                        {{ s }}
                    </option>
                </select>
            </div>

            <!-- Priority Icon Dropdown -->
            <div class="relative flex items-center shrink-0">
                <Flag class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                <select
                    :value="filters.priority || ''"
                    @change="updatePriority(($event.target as HTMLSelectElement).value)"
                    class="select select-bordered select-xs h-8 pl-8 pr-6 text-xs bg-background text-foreground border-sidebar-border rounded-lg focus:select-primary cursor-pointer shrink-0 min-w-[110px]"
                >
                    <option value="" class="bg-background text-foreground">Priority: All</option>
                    <option v-for="p in priorities" :key="p" :value="p" class="bg-background text-foreground">
                        {{ p }} Priority
                    </option>
                </select>
            </div>

            <!-- Sort Icon Dropdown -->
            <div class="relative flex items-center shrink-0">
                <ArrowUpDown class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                <select
                    :value="currentSortValue"
                    @change="updateSort(($event.target as HTMLSelectElement).value)"
                    class="select select-bordered select-xs h-8 pl-8 pr-6 text-xs bg-background text-foreground border-sidebar-border rounded-lg focus:select-primary cursor-pointer shrink-0 min-w-[115px]"
                >
                    <option value="created_at" class="bg-background text-foreground">Sort: Newest</option>
                    <option value="due_date" class="bg-background text-foreground">Due: Earliest</option>
                    <option value="-due_date" class="bg-background text-foreground">Due: Latest</option>
                    <option value="start_date" class="bg-background text-foreground">Start: Earliest</option>
                    <option value="client_name" class="bg-background text-foreground">Client A-Z</option>
                    <option value="project_name" class="bg-background text-foreground">Project A-Z</option>
                </select>
            </div>

            <!-- Reset Button -->
            <button
                v-if="hasActiveFilters"
                @click="emit('reset')"
                class="btn btn-ghost btn-xs text-destructive gap-1 hover:bg-destructive/10 h-8 px-2 shrink-0"
                title="Clear all active filters"
            >
                <RotateCcw class="h-3 w-3" />
                <span>Reset</span>
            </button>
        </div>

        <!-- Right Side: View Toggle & Counters -->
        <div class="flex items-center gap-3 text-xs text-muted-foreground shrink-0 justify-between md:justify-end">
            <div class="flex items-center gap-1.5 font-medium">
                <span>
                    Showing <strong class="text-foreground font-semibold">{{ filteredCount }}</strong> of <strong class="text-foreground font-semibold">{{ totalCount }}</strong>
                </span>
            </div>

            <!-- View Toggle -->
            <div class="join border border-sidebar-border rounded-lg bg-background">
                <button
                    @click="emit('update:viewMode', 'table')"
                    class="join-item btn btn-xs px-2.5 h-8"
                    :class="viewMode === 'table' ? 'btn-neutral' : 'btn-ghost text-muted-foreground hover:text-foreground'"
                    title="Table View"
                >
                    <LayoutList class="h-3.5 w-3.5" />
                </button>
                <button
                    @click="emit('update:viewMode', 'grid')"
                    class="join-item btn btn-xs px-2.5 h-8"
                    :class="viewMode === 'grid' ? 'btn-neutral' : 'btn-ghost text-muted-foreground hover:text-foreground'"
                    title="Grid View"
                >
                    <LayoutGrid class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>
    </div>
</template>
