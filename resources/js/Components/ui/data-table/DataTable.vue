<script setup>
import { ref, computed } from 'vue';
import { ArrowUpDown, ArrowUp, ArrowDown, ChevronsUpDown } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import Button from '@/Components/ui/button/Button.vue';

const props = defineProps({
    columns: { type: Array, required: true },
    data: { type: Array, required: true },
    pagination: { type: Object, default: () => ({ page: 1, perPage: 15, total: 0 }) },
    loading: { type: Boolean, default: false },
    emptyMessage: { type: String, default: 'No data available.' },
});

const emit = defineEmits(['page-change', 'sort']);

const sortKey = ref('');
const sortOrder = ref('asc');

function toggleSort(key) {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
    emit('sort', { key: sortKey.value, order: sortOrder.value });
}

const sortedData = computed(() => {
    if (!sortKey.value) return props.data;
    return [...props.data].sort((a, b) => {
        const aVal = a[sortKey.value];
        const bVal = b[sortKey.value];
        const cmp = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
        return sortOrder.value === 'asc' ? cmp : -cmp;
    });
});

const totalPages = computed(() =>
    props.pagination.perPage > 0
        ? Math.ceil(props.pagination.total / props.pagination.perPage)
        : 1,
);

function goToPage(page) {
    if (page < 1 || page > totalPages.value) return;
    emit('page-change', page);
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- Table -->
        <div class="rounded-md border bg-card">
            <table class="w-full caption-bottom text-sm">
                <thead class="border-b bg-muted/40">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            class="h-12 px-4 text-left align-middle font-medium text-muted-foreground cursor-pointer select-none hover:text-foreground transition-colors"
                            :class="[col.class || '', col.sortable ? 'pr-8' : '']"
                            :style="{ width: col.width || 'auto' }"
                            @click="col.sortable && toggleSort(col.key)"
                        >
                            <div class="flex items-center gap-1.5">
                                {{ col.label }}
                                <span v-if="col.sortable" class="ml-auto">
                                    <ArrowUp v-if="sortKey === col.key && sortOrder === 'asc'" class="h-4 w-4 text-primary" />
                                    <ArrowDown v-else-if="sortKey === col.key && sortOrder === 'desc'" class="h-4 w-4 text-primary" />
                                    <ChevronsUpDown v-else class="h-4 w-4 opacity-40" />
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="loading">
                        <tr v-for="i in pagination.perPage" :key="i" class="border-b">
                            <td v-for="col in columns" :key="col.key" class="p-4">
                                <div class="h-4 w-20 rounded bg-muted animate-pulse" />
                            </td>
                        </tr>
                    </template>
                    <template v-else-if="sortedData.length === 0">
                        <tr>
                            <td :colspan="columns.length" class="h-32 text-center text-muted-foreground">
                                {{ emptyMessage }}
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr
                            v-for="(row, idx) in sortedData"
                            :key="row.id ?? idx"
                            class="border-b transition-colors hover:bg-muted/30 data-[state=selected]:bg-muted"
                        >
                            <td
                                v-for="col in columns"
                                :key="col.key"
                                class="px-4 py-3 align-middle"
                            >
                                <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                                    {{ row[col.key] ?? '—' }}
                                </slot>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > 0" class="flex items-center justify-between px-2">
            <p class="text-sm text-muted-foreground">
                Showing {{ (pagination.page - 1) * pagination.perPage + 1 }}
                to {{ Math.min(pagination.page * pagination.perPage, pagination.total) }}
                of {{ pagination.total }} results
            </p>
            <div class="flex gap-1">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="pagination.page <= 1"
                    @click="goToPage(pagination.page - 1)"
                >
                    Prev
                </Button>
                <Button
                    v-for="p in Array.from({ length: totalPages }, (_, i) => i + 1).filter(
                        (p) => p === 1 || p === totalPages || Math.abs(p - pagination.page) <= 2,
                    )"
                    :key="p"
                    :variant="p === pagination.page ? 'default' : 'outline'"
                    size="sm"
                    @click="goToPage(p)"
                >
                    {{ p }}
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="pagination.page >= totalPages"
                    @click="goToPage(pagination.page + 1)"
                >
                    Next
                </Button>
            </div>
        </div>
    </div>
</template>
