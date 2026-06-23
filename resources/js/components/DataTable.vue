<template>
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-dark-card border-card">
    <!-- Header: Custom actions & Title -->
    <div v-if="title || subtitle || $slots.actions"
      class="card-header border-bottom border-secondary-custom py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
      <div v-if="title || subtitle">
        <h5 v-if="title" class="fw-bold text-white mb-1" style="font-size: 16px;">{{ title }}</h5>
        <div v-if="subtitle" class="text-muted small" v-html="subtitle"></div>
      </div>
      <div class="d-flex gap-2 flex-wrap ms-auto">
        <slot name="actions"></slot>
      </div>
    </div>

    <!-- Table content -->
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 table-premium">
        <thead class="text-muted small">
          <!-- Primary Header Titles -->
          <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <th v-for="header in headerGroup.headers" :key="header.id"
              :style="{ 
                width: header.column.columnDef.meta?.width || (shouldStretch(header.column.columnDef) ? 'auto' : '1%'),
                minWidth: header.column.columnDef.meta?.width || (shouldStretch(header.column.columnDef) ? 'auto' : (isSearchDisabled(header.column.columnDef) ? 'auto' : '120px'))
              }"
              class="fw-bold text-center align-middle">
              <span v-if="!header.isPlaceholder">
                <flex-render :render="header.column.columnDef.header" :props="header.getContext()" />
              </span>
            </th>
          </tr>
          <!-- Column-specific Search Filter Row -->
          <tr v-for="headerGroup in table.getHeaderGroups()" :key="'filter-' + headerGroup.id" class="table-search">
            <th v-for="header in headerGroup.headers" :key="'filter-cell-' + header.id"
              :style="{ 
                width: header.column.columnDef.meta?.width || (shouldStretch(header.column.columnDef) ? 'auto' : '1%'),
                minWidth: header.column.columnDef.meta?.width || (shouldStretch(header.column.columnDef) ? 'auto' : (isSearchDisabled(header.column.columnDef) ? 'auto' : '120px'))
              }"
              class="p-2 border-bottom border-secondary-custom align-middle">
              <div
                v-if="!header.isPlaceholder && header.column.getCanFilter() && !isSearchDisabled(header.column.columnDef)"
                class="w-100 px-1">
                <input type="text" class="form-control form-control-sm rounded-3 w-100 bg-dark-custom text-white border-secondary-custom" placeholder="Search..."
                  :value="header.column.getFilterValue() || ''"
                  @input="header.column.setFilterValue($event.target.value)" :style="{
                    fontSize: '13px',
                    fontWeight: 'normal',
                    padding: '6px 12px',
                    width: '100%'
                  }" />
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td :colspan="columns.length" class="text-center py-5">
              <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
              <span class="text-muted small">Loading records...</span>
            </td>
          </tr>
          <tr v-else-if="table.getRowModel().rows.length === 0">
            <td :colspan="columns.length" class="text-center py-5 text-muted small">
              <i class="bi bi-inbox fs-3 mb-2 d-block"></i>
              No records found.
            </td>
          </tr>
          <template v-else>
            <tr v-for="row in table.getRowModel().rows" :key="row.id">
              <td v-for="cell in row.getVisibleCells()" :key="cell.id"
                :class="isCentered(cell.column.columnDef) ? 'text-center' : 'text-start'"
                :style="{ 
                  width: cell.column.columnDef.meta?.width || (shouldStretch(cell.column.columnDef) ? 'auto' : '1%'),
                  minWidth: cell.column.columnDef.meta?.width || (shouldStretch(cell.column.columnDef) ? 'auto' : (isSearchDisabled(cell.column.columnDef) ? 'auto' : '120px'))
                }">
                <!-- Slot support for custom column overrides -->
                <slot :name="`cell(${cell.column.id})`" :value="cell.getValue()" :row="row.original" :index="row.index">
                  <span v-if="cell.column.id === 'no' || cell.column.id === 'index'">
                    {{ (table.getState().pagination.pageIndex * table.getState().pagination.pageSize) + row.index + 1 }}
                  </span>
                  <flex-render v-else :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                </slot>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer matching Delivery-Update-V2 exactly -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3 px-3 pb-3">
      <!-- Entries Info -->
      <div class="text-muted small fw-500">
        Showing {{ table.getFilteredRowModel().rows.length > 0 ? table.getState().pagination.pageIndex *
          table.getState().pagination.pageSize + 1 : 0 }}
        to {{ Math.min((table.getState().pagination.pageIndex + 1) * table.getState().pagination.pageSize,
          table.getFilteredRowModel().rows.length) }}
        of {{ table.getFilteredRowModel().rows.length }} entries
      </div>

      <div class="d-flex align-items-center gap-3">
        <!-- Rows per page -->
        <div class="d-flex align-items-center">
          <span class="me-2 small text-muted">Rows per page:</span>
          <select class="form-select form-select-sm border-secondary-custom bg-dark-custom text-white rounded-3"
            :value="table.getState().pagination.pageSize" @change="e => table.setPageSize(Number(e.target.value))"
            style="width: auto;">
            <option v-for="size in [10, 25, 50, 100]" :key="size" :value="size">{{ size }}</option>
          </select>
        </div>

        <!-- Navigation -->
        <nav aria-label="Page navigation">
          <ul class="pagination pagination-sm mb-0">
            <!-- First Page -->
            <li class="page-item" :class="{ disabled: !table.getCanPreviousPage() }">
              <button class="page-link" @click="table.setPageIndex(0)" :disabled="!table.getCanPreviousPage()"
                title="First Page">
                <i class="bi bi-chevron-double-left" style="font-size: 12px;"></i>
              </button>
            </li>
            <!-- Previous Page -->
            <li class="page-item" :class="{ disabled: !table.getCanPreviousPage() }">
              <button class="page-link" @click="table.previousPage()" :disabled="!table.getCanPreviousPage()"
                title="Previous Page">
                <i class="bi bi-chevron-left" style="font-size: 12px;"></i>
              </button>
            </li>
            <!-- Page Indicator -->
            <li class="page-item disabled">
              <span class="page-link bg-dark-custom text-primary fw-bold px-3 border-secondary-custom">
                {{ table.getState().pagination.pageIndex + 1 }} / {{ table.getPageCount() || 1 }}
              </span>
            </li>
            <!-- Next Page -->
            <li class="page-item" :class="{ disabled: !table.getCanNextPage() }">
              <button class="page-link" @click="table.nextPage()" :disabled="!table.getCanNextPage()" title="Next Page">
                <i class="bi bi-chevron-right" style="font-size: 12px;"></i>
              </button>
            </li>
            <!-- Last Page -->
            <li class="page-item" :class="{ disabled: !table.getCanNextPage() }">
              <button class="page-link" @click="table.setPageIndex(table.getPageCount() - 1)"
                :disabled="!table.getCanNextPage()" title="Last Page">
                <i class="bi bi-chevron-double-right" style="font-size: 12px;"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import {
  useVueTable,
  getCoreRowModel,
  getPaginationRowModel,
  getFilteredRowModel,
  FlexRender
} from '@tanstack/vue-table';

const props = defineProps({
  data: {
    type: Array,
    required: true
  },
  columns: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  searchPlaceholder: {
    type: String,
    default: 'Search records...'
  },
  pagination: {
    type: Boolean,
    default: true
  },
  pageSize: {
    type: Number,
    default: 10
  },
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  }
});

const globalFilter = ref('');
const columnFilters = ref([]);

// Maintain local pagination state
const paginationState = ref({
  pageIndex: 0,
  pageSize: props.pageSize
});

const table = useVueTable({
  get data() { return props.data },
  get columns() { return props.columns },
  state: {
    get globalFilter() { return globalFilter.value },
    get columnFilters() { return columnFilters.value },
    get pagination() { return paginationState.value }
  },
  onPaginationChange: (updater) => {
    if (typeof updater === 'function') {
      paginationState.value = updater(paginationState.value);
    } else {
      paginationState.value = updater;
    }
  },
  onColumnFiltersChange: (updater) => {
    if (typeof updater === 'function') {
      columnFilters.value = updater(columnFilters.value);
    } else {
      columnFilters.value = updater;
    }
  },
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getFilteredRowModel: getFilteredRowModel()
});

// Reset page index if filters change or data changes
watch([globalFilter, columnFilters, () => props.data], () => {
  paginationState.value.pageIndex = 0;
});

const shouldStretch = (columnDef) => {
  if (columnDef.meta?.stretch) return true;
  const id = (columnDef.id || columnDef.accessorKey || '').toLowerCase();
  return id.includes('description') || id.includes('project') || id.includes('name') || id.includes('address') || id.includes('detail');
};

const isSearchDisabled = (columnDef) => {
  if (columnDef.meta?.disableSearch) return true;
  const id = (columnDef.id || columnDef.accessorKey || '').toLowerCase();
  const header = typeof columnDef.header === 'string' ? columnDef.header.toLowerCase() : '';
  return ['index', 'actions', 'action', 'no'].includes(id) || ['no', 'action', 'actions'].includes(header);
};

const isCentered = (columnDef) => {
  if (columnDef.meta?.align === 'center') return true;
  if (columnDef.meta?.align === 'left' || columnDef.meta?.align === 'right') return false;
  const id = (columnDef.id || columnDef.accessorKey || '').toLowerCase();
  const header = typeof columnDef.header === 'string' ? columnDef.header.toLowerCase() : '';
  return ['index', 'actions', 'action', 'no', 'volume', 'uom', 'qty', 'locator', 'created_at', 'create date'].includes(id) ||
    ['no', 'volume', 'uom', 'qty', 'locator', 'created_at', 'create date', 'action', 'actions'].includes(header);
};
</script>

<style scoped>
.table {
  font-family: inherit;
}

.table-premium th,
.table-premium td {
  white-space: nowrap;
}



.table-premium td.allow-wrap,
.table-premium td .line-clamp-3 {
  white-space: normal !important;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.5;
  max-height: 4.5em;
}

.table-search th {
  background-color: #111827 !important;
}

.table-responsive {
  overflow-x: auto;
  position: relative;
}

.page-link {
  cursor: pointer;
  border-radius: 0.35rem;
  margin: 0 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
  transition: all 0.2s ease;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background-color: rgba(10, 15, 26, 0.6);
  color: #0d6efd;
}

.page-link:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: #0a58ca;
}

.page-item.disabled .page-link {
  cursor: not-allowed;
  background-color: rgba(17, 24, 39, 0.5);
  color: #6c757d;
  border-color: rgba(255, 255, 255, 0.05);
}

.page-item.disabled .page-link.bg-dark-custom {
  background-color: rgba(10, 15, 26, 0.6) !important;
  color: #0d6efd !important;
  border-color: rgba(255, 255, 255, 0.08) !important;
  opacity: 1;
}

.bg-dark-card {
  background-color: #111827 !important;
}

.border-card {
  border: 1px solid rgba(255, 255, 255, 0.05) !important;
  border-radius: 12px;
}

.bg-dark-custom {
  background-color: rgba(10, 15, 26, 0.6) !important;
}

.border-secondary-custom {
  border-color: rgba(255, 255, 255, 0.08) !important;
}
</style>
