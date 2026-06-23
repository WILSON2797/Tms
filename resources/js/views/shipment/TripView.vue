<template>
  <div class="trip-view">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h3 class="fw-bold text-white mb-1">Trip List (Daftar Trip)</h3>
        <p class="text-muted">Kelola dan pantau seluruh trip perjalanan yang sudah terdaftar, status pengiriman, penugasan driver, dan armada.</p>
      </div>
    </div>

    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="trips" 
      :loading="loading" 
      empty-text="Belum ada rencana trip pengiriman."
    >
      <template #cell(trip_number)="{ value }">
        <span class="text-info fw-bold">{{ value }}</span>
      </template>
      <template #cell(mot)="{ value }">
        <span class="text-white">{{ value }}</span>
      </template>
      <template #cell(mod)="{ value }">
        <span class="text-info fw-bold">{{ value }}</span>
      </template>
      <template #cell(shipment_orders)="{ value }">
        <span class="text-white">{{ value ? value.length : 0 }} Order</span>
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
            class="btn btn-sm btn-outline-info border-0" 
            @click="openDetailModal(row)"
            title="Lihat Detail Trip"
          >
            <i class="bi bi-eye"></i>
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
      <div class="modal-dialog-custom bg-dark-card border-card p-4 rounded-4 shadow-lg text-white">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary-custom pb-2">
          <h5 class="fw-bold text-white mb-0">
            <i class="bi bi-truck text-primary me-2"></i>
            Detail Trip - {{ selectedTrip?.trip_number }}
          </h5>
          <button type="button" class="btn-close btn-close-white" @click="closeDetailModal"></button>
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
              <strong class="text-white">{{ formatDate(selectedTrip?.trip_date) }}</strong>
            </div>
            <div class="col-6 col-md-4">
              <span class="text-muted d-block mb-1">STATUS TRIP</span>
              <span class="badge" :class="getStatusBadgeClass(selectedTrip?.status)">{{ selectedTrip?.status }}</span>
            </div>
            <div class="col-6 col-md-4">
              <span class="text-muted d-block mb-1">MODE OF TRANSPORT (MOT)</span>
              <strong class="text-white">{{ selectedTrip?.mot }}</strong>
            </div>
            <div class="col-6 col-md-4">
              <span class="text-muted d-block mb-1">MODE OF DELIVERY (MOD)</span>
              <strong class="text-white">{{ selectedTrip?.mod }}</strong>
            </div>
            <div class="col-12 col-md-8">
              <span class="text-muted d-block mb-1">TRANSPORTER (VENDOR)</span>
              <strong class="text-white">{{ selectedTrip?.transporter?.transporter_name || '-' }}</strong>
            </div>
            <div class="col-6">
              <span class="text-muted d-block mb-1">DRIVER (SUPIR)</span>
              <strong class="text-white">{{ selectedTrip?.driver?.driver_name || '-' }}</strong>
            </div>
            <div class="col-6">
              <span class="text-muted d-block mb-1">VEHICLE (NO. KENDARAAN)</span>
              <strong class="text-white">{{ selectedTrip?.vehicle?.vehicle_no || '-' }} ({{ selectedTrip?.vehicle?.vehicle_type || '-' }})</strong>
            </div>
          </div>

          <!-- Consolidated Orders list -->
          <h6 class="fw-bold text-white mb-2">Shipment Orders yang Dikonsolidasikan (Console)</h6>
          <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
            <table class="table table-dark table-hover mb-0 align-middle table-sm small">
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
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
});

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
.trip-view {
  background-color: #0b0f19;
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
