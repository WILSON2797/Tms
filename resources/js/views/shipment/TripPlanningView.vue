<template>
  <div class="trip-planning-view">
    <div class="mb-4">
      <h3 class="fw-bold text-gray-900 mb-1">Waiting Dispatch (Menunggu Pengiriman)</h3>
      <p class="text-muted">Pilih satu atau beberapa shipment order di bawah ini, lalu klik tombol untuk membuat rencana trip baru secara langsung.</p>
    </div>

    <!-- Unassigned Orders Pool DataTable -->
    <DataTable 
      :columns="columns" 
      :data="unassignedOrders" 
      :loading="loading" 
      empty-text="Tidak ada shipment order DRAFT yang belum ditugaskan."
    >
      <!-- Top header actions slot in DataTable -->
      <template #actions>
        <button 
          type="button" 
          class="btn btn-primary d-flex align-items-center gap-2"
          :disabled="selectedOrderIds.length === 0"
          @click="openCreateTripModal"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Buat Rencana Trip ({{ selectedOrderIds.length }} Terpilih)</span>
        </button>
      </template>

      <!-- Custom Cell Renderers -->
      <template #cell(action_select)="{ row }">
        <div class="form-check d-flex justify-content-center">
          <input 
            type="checkbox" 
            class="form-check-input" 
            :value="row.id" 
            :checked="selectedOrderIds.includes(row.id)"
            @change="toggleOrderSelection(row.id, $event.target.checked)"
          />
        </div>
      </template>

      <template #cell(order_number)="{ row }">
        <div class="text-nowrap">
          <div class="fw-bold text-info">{{ row.order_number }}</div>
          <div class="small text-muted">{{ row.job_number }}</div>
        </div>
      </template>

      <template #cell(origin_city)="{ value }">
        <span class="text-gray-900">{{ value }}</span>
      </template>

      <template #cell(destination_city)="{ value }">
        <span class="text-gray-900 fw-semibold">{{ value }}</span>
      </template>

      <template #cell(detail_address)="{ value }">
        <div class="text-muted line-clamp-3" style="min-width: 280px; max-width: 450px;" :title="value">
          {{ value }}
        </div>
      </template>

      <template #cell(order_type)="{ value }">
        <span :class="value === 'URGENT' ? 'text-danger fw-semibold' : 'text-info fw-semibold'">
          {{ value }}
        </span>
      </template>
    </DataTable>

    <!-- Create Trip Modal (Direct Modal UX - Premium design) -->
    <!-- Create Trip Modal (Direct Modal UX - Premium design) -->
    <Teleport to="body">
      <div 
        v-if="showModal" 
        class="modal-backdrop-custom d-flex align-items-center justify-content-center"
        @click.self="closeCreateTripModal"
      >
        <div class="modal-dialog-custom bg-dark-card border-card p-4 rounded-4 shadow-lg text-gray-900">
          <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary-custom pb-2">
            <h5 class="fw-bold text-gray-900 mb-0">
              <i class="bi bi-truck text-primary me-2"></i>
              Buat Rencana Trip ({{ selectedOrderIds.length }} Order Terpilih)
            </h5>
            <button type="button" class="btn-close" @click="closeCreateTripModal"></button>
          </div>

          <form @submit.prevent="handleSubmit">
            <div class="row g-3">
              <!-- Trip Date -->
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">TANGGAL TRIP <span class="text-danger">*</span></label>
                <VueDatePicker
                  v-model="form.trip_date"
                  model-type="yyyy-MM-dd"
                  :enable-time-picker="false"
                  placeholder="Pilih Tanggal Trip..."
                  :clearable="false"
                  auto-position="bottom"
                  auto-apply
                  format="dd/MM/yyyy"
                />
              </div>

              <!-- Mode Of Transport (MOT) -->
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">MODE OF TRANSPORT (MOT) <span class="text-danger">*</span></label>
                <v-select
                  v-model="form.mot_id"
                  :options="mots"
                  label="name"
                  :reduce="m => m.id"
                  placeholder="Pilih Mode Of Transport..."
                  :clearable="false"
                  append-to-body
                />
              </div>

              <!-- Mode Of Delivery (MOD) -->
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">MODE OF DELIVERY (MOD) <span class="text-danger">*</span></label>
                <v-select
                  v-model="form.mod_id"
                  :options="mods"
                  label="name"
                  :reduce="m => m.id"
                  placeholder="Pilih Mode Of Delivery..."
                  :clearable="false"
                  append-to-body
                />
              </div>

              <!-- Transporter Vendor -->
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">TRANSPORTER (VENDOR)</label>
                <v-select
                  v-model="form.transporter_id"
                  :options="transporters"
                  label="transporter_name"
                  :reduce="t => t.id"
                  placeholder="Pilih Transporter..."
                  append-to-body
                />
              </div>

              <!-- Driver -->
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">DRIVER (SUPIR) <span class="text-danger">*</span></label>
                <v-select
                  v-model="form.driver_id"
                  :options="drivers"
                  label="driver_name"
                  :reduce="d => d.id"
                  placeholder="Pilih Driver..."
                  :clearable="false"
                  append-to-body
                />
              </div>

              <!-- Vehicle -->
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">VEHICLE (ARMADA TRUK) <span class="text-danger">*</span></label>
                <v-select
                  v-model="form.vehicle_id"
                  :options="vehicles"
                  label="label"
                  :reduce="v => v.id"
                  placeholder="Pilih Kendaraan..."
                  :clearable="false"
                  append-to-body
                />
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top border-secondary-custom mt-4 pt-3">
              <button type="button" class="btn btn-secondary" @click="closeCreateTripModal" :disabled="submitLoading">
                Batal
              </button>
              <button type="submit" class="btn btn-primary d-flex align-items-center gap-2" :disabled="submitLoading">
                <span v-if="submitLoading" class="spinner-border spinner-border-sm" role="status"></span>
                <i class="bi bi-send"></i>
                <span>Simpan Rencana Trip</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, h } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import DataTable from '../../components/DataTable.vue';

const toast = useToast();

const loading = ref(false);
const submitLoading = ref(false);
const unassignedOrders = ref([]);
const selectedOrderIds = ref([]);

const showModal = ref(false);

const mots = ref([]);
const mods = ref([]);
const transporters = ref([]);
const drivers = ref([]);
const vehicles = ref([]);

const form = reactive({
  trip_date: new Date().toISOString().split('T')[0],
  mot_id: '',
  mod_id: '',
  transporter_id: '',
  driver_id: '',
  vehicle_id: '',
});

// Computed dynamic reactive columns config for TanStack Table
const columns = computed(() => [
  {
    accessorKey: 'no',
    header: 'No',
    meta: { disableSearch: true, width: '55px', align: 'center' }
  },
  {
    accessorKey: 'order_number',
    header: 'No. Order / Job Number',
  },
  {
    accessorKey: 'customer.customer_name',
    header: 'Customer',
  },
  {
    accessorKey: 'origin_city',
    header: 'Origin',
  },
  {
    accessorKey: 'destination_city',
    header: 'Destination',
  },
  {
    accessorKey: 'detail_address',
    header: 'Detail Address',
  },
  {
    accessorKey: 'order_type',
    header: 'Order Type',
  },
  {
    accessorKey: 'action_select',
    header: () => h('div', { class: 'd-flex align-items-center justify-content-center gap-2' }, [
      h('span', { class: 'fw-bold text-uppercase' }, 'Pilih'),
      h('input', {
        type: 'checkbox',
        class: 'form-check-input m-0',
        checked: isAllSelected.value,
        onChange: (e) => { isAllSelected.value = e.target.checked }
      })
    ]),
    meta: { disableSearch: true, width: '130px', align: 'center' }
  }
]);

const isAllSelected = computed({
  get: () => {
    return unassignedOrders.value.length > 0 && unassignedOrders.value.every(o => selectedOrderIds.value.includes(o.id));
  },
  set: (val) => {
    if (val) {
      unassignedOrders.value.forEach(o => {
        if (!selectedOrderIds.value.includes(o.id)) {
          selectedOrderIds.value.push(o.id);
        }
      });
    } else {
      unassignedOrders.value.forEach(o => {
        const idx = selectedOrderIds.value.indexOf(o.id);
        if (idx > -1) {
          selectedOrderIds.value.splice(idx, 1);
        }
      });
    }
  }
});

const toggleOrderSelection = (orderId, isChecked) => {
  if (isChecked) {
    if (!selectedOrderIds.value.includes(orderId)) {
      selectedOrderIds.value.push(orderId);
    }
  } else {
    const idx = selectedOrderIds.value.indexOf(orderId);
    if (idx > -1) {
      selectedOrderIds.value.splice(idx, 1);
    }
  }
};

onMounted(() => {
  fetchUnassignedOrders();
  fetchMasterData();
});

const fetchUnassignedOrders = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/shipment-orders/unassigned');
    if (response.data.success) {
      unassignedOrders.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data shipment order unassigned.');
  } finally {
    loading.value = false;
  }
};

const fetchMasterData = async () => {
  try {
    const [motRes, modRes, trspRes, drvRes, vehRes] = await Promise.all([
      axios.get('/modes-of-transport'),
      axios.get('/modes-of-delivery'),
      axios.get('/transporters'),
      axios.get('/drivers'),
      axios.get('/vehicles')
    ]);
    mots.value = motRes.data.data;
    mods.value = modRes.data.data;
    transporters.value = trspRes.data.data.filter(t => t.is_active);
    drivers.value = drvRes.data.data.filter(d => d.is_active);
    vehicles.value = vehRes.data.data.filter(v => v.is_active).map(v => ({
      ...v,
      label: `${v.vehicle_no} - ${v.vehicle_type} (${v.brand})`
    }));
  } catch (err) {
    toast.error('Gagal mengambil data referensi master.');
  }
};

const openCreateTripModal = () => {
  if (selectedOrderIds.value.length === 0) return;
  // Reset form values to default
  form.trip_date = new Date().toISOString().split('T')[0];
  form.mot_id = '';
  form.mod_id = '';
  form.transporter_id = '';
  form.driver_id = '';
  form.vehicle_id = '';
  showModal.value = true;
};

const closeCreateTripModal = () => {
  showModal.value = false;
};

const handleSubmit = async () => {
  if (selectedOrderIds.value.length === 0) {
    toast.warning('Silakan pilih minimal 1 Shipment Order!');
    return;
  }

  const payload = {
    ...form,
    transporter_id: form.transporter_id || null,
    shipment_order_ids: selectedOrderIds.value
  };

  submitLoading.value = true;
  try {
    const response = await axios.post('/trips', payload);
    if (response.data.success) {
      toast.success(response.data.message);
      closeCreateTripModal();
      selectedOrderIds.value = []; // Reset checkboxes
      fetchUnassignedOrders(); // Reload unassigned orders pool
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan rencana trip.');
  } finally {
    submitLoading.value = false;
  }
};
</script>

<style scoped>

/* Modal styles */
.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(4px);
  z-index: 1050;
}

.modal-dialog-custom {
  width: 90%;
  max-width: 950px;
  margin: 1.75rem;
  max-height: 90vh;
  overflow-y: auto;
}

/* Custom scrollbar */
.table-responsive::-webkit-scrollbar {
  width: 6px;
}
.table-responsive::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.1);
}
.table-responsive::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
