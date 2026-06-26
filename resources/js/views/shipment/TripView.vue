<template>
  <div class="trip-view">
    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="trips" 
      :loading="loading" 
      empty-text="Belum ada rencana trip pengiriman."
      title="Trip List (Daftar Trip)"
      subtitle="Kelola dan pantau seluruh trip perjalanan yang sudah terdaftar, status pengiriman, penugasan driver, dan armada."
    >
      <template #cell(trip_number)="{ value }">
        <span class="text-info fw-bold">{{ value }}</span>
      </template>
      <template #cell(mot)="{ value }">
        <span class="text-gray-900">{{ value }}</span>
      </template>
      <template #cell(mod)="{ value }">
        <span class="text-info fw-bold">{{ value }}</span>
      </template>
      <template #cell(shipment_orders)="{ value }">
        <span class="text-gray-900">{{ value ? value.length : 0 }} Order</span>
      </template>
      <template #cell(status)="{ value }">
        <span class="badge" :class="getStatusBadgeClass(value)">
          {{ value }}
        </span>
      </template>
      <template #cell(actions)="{ row }">
        <div class="d-flex gap-2">
          <!-- View Detail Action Button -->
          <button 
            class="btn btn-sm border-0 p-1" 
            style="color: #0284c7;"
            @click="openDetailModal(row)"
            title="Lihat Detail Trip"
          >
            <i class="bi bi-eye fs-5"></i>
          </button>

          <!-- Re-assign Action Button -->
          <button 
            v-if="row.status !== 'DELIVERED' && row.status !== 'CANCELLED' && authStore.hasPermission('edit-shipment')"
            class="btn btn-sm border-0 p-1 text-warning-custom" 
            style="color: #d97706;"
            @click="openReassignModal(row)"
            title="Re-assign Driver/Vehicle"
          >
            <i class="bi bi-person-gear fs-5"></i>
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Trip Detail Modal (Glassmorphism & Premium UI) -->
    <div 
      v-if="showDetailModal" 
      class="modal-backdrop-custom d-flex align-items-center justify-content-center"
      @click.self="closeDetailModal"
    >
      <div class="modal-dialog-custom bg-dark-card border-card p-4 rounded-4 shadow-lg text-gray-900 dark:text-white">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary-custom pb-2">
          <h5 class="fw-bold text-gray-900 dark:text-white mb-0">
            <i class="bi bi-truck text-primary me-2"></i>
            Detail Trip - {{ selectedTrip?.trip_number }}
          </h5>
          <button type="button" class="btn-close dark:btn-close-white" @click="closeDetailModal"></button>
        </div>

        <div v-if="detailLoading" class="text-center py-5">
          <div class="spinner-border text-primary mb-2" role="status"></div>
          <p class="text-muted small">Memuat detail trip...</p>
        </div>

        <div v-else>
          <!-- Trip Meta Info Grid -->
          <div class="row g-3 small mb-4 bg-dark-custom p-3 rounded-3 border border-secondary-custom">
            <div class="col-6 col-md-4">
              <span class="text-muted d-block mb-1">TANGGAL TRIP</span>
              <strong class="text-gray-900 dark:text-white">{{ formatDate(selectedTrip?.trip_date) }}</strong>
            </div>
            <div class="col-6 col-md-4">
              <span class="text-muted d-block mb-1">STATUS TRIP</span>
              <span class="badge" :class="getStatusBadgeClass(selectedTrip?.status)">{{ selectedTrip?.status }}</span>
            </div>
            <div class="col-6 col-md-4">
              <span class="text-muted d-block mb-1">MODE OF TRANSPORT (MOT)</span>
              <strong class="text-gray-900 dark:text-white">{{ selectedTrip?.mot }}</strong>
            </div>
            <div class="col-6 col-md-4">
              <span class="text-muted d-block mb-1">MODE OF DELIVERY (MOD)</span>
              <strong class="text-gray-900 dark:text-white">{{ selectedTrip?.mod }}</strong>
            </div>
            <div class="col-12 col-md-8">
              <span class="text-muted d-block mb-1">TRANSPORTER (VENDOR)</span>
              <strong class="text-gray-900 dark:text-white">{{ selectedTrip?.transporter?.transporter_name || '-' }}</strong>
            </div>
            <div class="col-6">
              <span class="text-muted d-block mb-1">DRIVER (SUPIR)</span>
              <strong class="text-gray-900 dark:text-white">{{ selectedTrip?.driver?.driver_name || '-' }}</strong>
            </div>
            <div class="col-6">
              <span class="text-muted d-block mb-1">VEHICLE (NO. KENDARAAN)</span>
              <strong class="text-gray-900 dark:text-white">{{ selectedTrip?.vehicle?.vehicle_no || '-' }} ({{ selectedTrip?.vehicle?.vehicle_type || '-' }})</strong>
            </div>
          </div>

          <!-- Consolidated Orders list -->
          <h6 class="fw-bold text-gray-900 dark:text-white mb-2">Shipment Orders yang Dikonsolidasikan (Console)</h6>
          <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-hover mb-0 align-middle table-sm small">
              <thead class="bg-dark-custom text-muted sticky-top">
                <tr>
                  <th>Job / Order No.</th>
                  <th>Customer</th>
                  <th>Rute</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!selectedTrip?.shipment_orders || selectedTrip.shipment_orders.length === 0">
                  <td colspan="4" class="text-center py-3 text-muted">Tidak ada shipment order di dalam trip ini.</td>
                </tr>
                <tr v-else v-for="order in selectedTrip.shipment_orders" :key="order.id">
                  <td>
                    <div class="fw-bold text-info">{{ order.order_number }}</div>
                    <div class="text-muted xx-small">{{ order.job_number }}</div>
                  </td>
                  <td>{{ order.customer?.customer_name }}</td>
                  <td>
                    <div class="fw-semibold">{{ order.origin_city }} <i class="bi bi-arrow-right text-muted"></i> {{ order.destination_city }}</div>
                  </td>
                  <td>
                    <span class="badge badge-sm" :class="getOrderStatusBadgeClass(order.status)">{{ order.status }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="d-flex justify-content-end border-top border-secondary-custom mt-4 pt-3">
          <button type="button" class="btn btn-secondary" @click="closeDetailModal">
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Re-assign Driver/Vehicle Modal -->
    <Teleport to="body">
      <div 
        v-if="showReassignModal" 
        class="modal-backdrop-custom d-flex align-items-center justify-content-center"
        @click.self="closeReassignModal"
      >
        <div class="modal-dialog-custom bg-dark-card border-card p-4 rounded-4 shadow-lg text-gray-900 dark:text-white" style="max-width: 550px;">
          <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary-custom pb-2">
            <h5 class="fw-bold text-gray-900 dark:text-white mb-0">
              <i class="bi bi-person-gear text-warning me-2"></i>
              Re-assign Driver & Kendaraan - {{ selectedTripForReassign?.trip_number }}
            </h5>
            <button type="button" class="btn-close dark:btn-close-white" @click="closeReassignModal"></button>
          </div>

          <form @submit.prevent="handleReassign">
            <div class="row g-3">
              <!-- Driver -->
              <div class="col-12">
                <label class="form-label text-muted small mb-1 fw-bold">PILIH DRIVER BARU</label>
                <v-select
                  v-model="reassignForm.driver_id"
                  :options="drivers"
                  label="driver_name"
                  :reduce="d => d.id"
                  placeholder="Pilih Driver..."
                  :clearable="true"
                  append-to-body
                />
              </div>

              <!-- Vehicle -->
              <div class="col-12">
                <label class="form-label text-muted small mb-1 fw-bold">PILIH KENDARAAN BARU</label>
                <v-select
                  v-model="reassignForm.vehicle_id"
                  :options="vehicles"
                  label="label"
                  :reduce="v => v.id"
                  placeholder="Pilih Kendaraan..."
                  :clearable="true"
                  append-to-body
                />
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top border-secondary-custom mt-4 pt-3">
              <button type="button" class="btn btn-secondary" @click="closeReassignModal" :disabled="reassignLoading">
                Batal
              </button>
              <button type="submit" class="btn btn-warning text-dark d-flex align-items-center gap-2" :disabled="reassignLoading">
                <span v-if="reassignLoading" class="spinner-border spinner-border-sm" role="status"></span>
                <i class="bi bi-check2-circle"></i>
                <span class="fw-bold">Update Penugasan</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import DataTable from '../../components/DataTable.vue';

const authStore = useAuthStore();
const toast = useToast();

const loading = ref(false);
const trips = ref([]);

// Modal detail state
const showDetailModal = ref(false);
const detailLoading = ref(false);
const selectedTrip = ref(null);

// Reassign state
const showReassignModal = ref(false);
const reassignLoading = ref(false);
const selectedTripForReassign = ref(null);
const reassignForm = reactive({
  driver_id: null,
  vehicle_id: null
});
const drivers = ref([]);
const vehicles = ref([]);

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'trip_number', header: 'Trip Number' },
  { accessorKey: 'trip_date', header: 'Tgl Trip' },
  { accessorKey: 'mot', header: 'MOT' },
  { accessorKey: 'mod', header: 'MOD' },
  { accessorKey: 'transporter.transporter_name', header: 'Transporter' },
  { accessorKey: 'driver.driver_name', header: 'Driver' },
  { accessorKey: 'vehicle.vehicle_no', header: 'No. Kendaraan' },
  { accessorKey: 'shipment_orders', header: 'Jumlah Order' },
  { accessorKey: 'status', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

onMounted(() => {
  fetchTrips();
  fetchDriversAndVehicles();
});

const fetchDriversAndVehicles = async () => {
  try {
    const [drvRes, vehRes] = await Promise.all([
      axios.get('/drivers'),
      axios.get('/vehicles')
    ]);
    drivers.value = drvRes.data.data.filter(d => d.is_active);
    vehicles.value = vehRes.data.data.filter(v => v.is_active).map(v => ({
      ...v,
      label: `${v.vehicle_no} - ${v.vehicle_type} (${v.brand})`
    }));
  } catch (err) {
    toast.error('Gagal mengambil data driver dan kendaraan.');
  }
};

const fetchTrips = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/trips');
    if (response.data.success) {
      trips.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data trip planning.');
  } finally {
    loading.value = false;
  }
};

const openDetailModal = async (trip) => {
  selectedTrip.value = trip;
  showDetailModal.value = true;
  detailLoading.value = true;

  try {
    const response = await axios.get(`/trips/${trip.id}`);
    if (response.data.success) {
      selectedTrip.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil detail trip.');
  } finally {
    detailLoading.value = false;
  }
};

const closeDetailModal = () => {
  showDetailModal.value = false;
  selectedTrip.value = null;
};

const openReassignModal = (trip) => {
  selectedTripForReassign.value = trip;
  reassignForm.driver_id = trip.driver_id;
  reassignForm.vehicle_id = trip.vehicle_id;
  showReassignModal.value = true;
};

const closeReassignModal = () => {
  showReassignModal.value = false;
  selectedTripForReassign.value = null;
  reassignForm.driver_id = null;
  reassignForm.vehicle_id = null;
};

const handleReassign = async () => {
  if (!selectedTripForReassign.value) return;
  reassignLoading.value = true;
  try {
    const response = await axios.put(`/trips/${selectedTripForReassign.value.id}`, {
      driver_id: reassignForm.driver_id,
      vehicle_id: reassignForm.vehicle_id
    });
    if (response.data.success) {
      toast.success('Driver dan armada trip berhasil diperbarui.');
      closeReassignModal();
      fetchTrips();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal melakukan re-assign.');
  } finally {
    reassignLoading.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'DRAFT': return 'bg-secondary';
    case 'IN_TRANSIT': return 'bg-warning text-dark';
    case 'DELIVERED': return 'bg-success';
    case 'CANCELLED': return 'bg-danger';
    default: return 'bg-light text-dark';
  }
};

const getOrderStatusBadgeClass = (status) => {
  switch (status) {
    case 'DRAFT': return 'bg-secondary';
    case 'ASSIGNED': return 'bg-primary';
    case 'IN_TRANSIT': return 'bg-warning text-dark';
    case 'DELIVERED': return 'bg-success';
    case 'CANCELLED': return 'bg-danger';
    default: return 'bg-light text-dark';
  }
};
</script>

<style scoped>



.text-muted {
  color: #8c98a5 !important;
}

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
  width: 100%;
  max-width: 700px;
  margin: 1.75rem;
  max-height: 90vh;
  overflow-y: auto;
}

.xx-small {
  font-size: 0.7rem;
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
</style>
