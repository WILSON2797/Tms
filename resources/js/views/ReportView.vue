<template>
  <div class="report-view">
    <!-- Data Table -->
    <DataTable 
      :columns="columns" 
      :data="orders" 
      :loading="loading" 
      empty-text="Belum ada data laporan shipment order."
      title="Laporan TMS (Shipment Report)"
      subtitle="Data historis lengkap dari seluruh shipment order beserta status penugasan trip dan tonggak waktu (milestone)."
    >
      <template #actions>
        <button 
          class="btn btn-success d-flex align-items-center gap-2 rounded-3 py-2 px-3 border-0 shadow-sm"
          style="background-color: #10b981;"
          @click="exportToExcel"
          :disabled="loading"
          title="Download Laporan Excel"
        >
          <i class="bi bi-file-earmark-excel-fill"></i>
          <span class="fw-semibold small">Download XLSX</span>
        </button>
      </template>

      <template #cell(order_number)="{ value }">
        <span class="fw-bold text-gray-900">{{ value }}</span>
      </template>
      <template #cell(job_number)="{ value }">
        <span class="text-info fw-bold">{{ value }}</span>
      </template>
      
      <template #cell(origin)="{ row }">
        <div class="small">
          <div class="fw-semibold text-gray-900">{{ row.origin_city }}</div>
          <div class="text-muted xx-small">{{ row.origin_province }}</div>
        </div>
      </template>
      
      <template #cell(destination)="{ row }">
        <div class="small">
          <div class="fw-semibold text-gray-900">{{ row.destination_city }}</div>
          <div class="text-muted xx-small">{{ row.destination_province }}</div>
        </div>
      </template>

      <template #cell(status)="{ value }">
        <span class="badge" :class="getStatusBadgeClass(value)">
          {{ value }}
        </span>
      </template>

      <template #cell(driver)="{ row }">
        <span v-if="row.trip && row.trip.driver" class="text-gray-900 small">{{ row.trip.driver.driver_name }}</span>
        <span v-else class="text-muted">-</span>
      </template>

      <template #cell(vehicle)="{ row }">
        <span v-if="row.trip && row.trip.vehicle" class="text-gray-900 small">{{ row.trip.vehicle.vehicle_no }}</span>
        <span v-else class="text-muted">-</span>
      </template>


      <template #cell(pod_info)="{ row }">
        <div class="d-flex gap-2">
          <!-- Button View Foto POD -->
          <button 
            v-if="row.pod_photo_path" 
            class="btn btn-sm border-0 p-1" 
            style="color: #0284c7;"
            @click="openImageViewer('/storage/' + row.pod_photo_path, 'Foto Dokumentasi POD', row.pod_recipient_name)"
            title="Lihat Foto POD"
          >
            <i class="bi bi-image fs-5"></i>
          </button>
          
          <!-- Button View TTD POD -->
          <button 
            v-if="row.pod_signature_path" 
            class="btn btn-sm border-0 p-1" 
            style="color: #10b981;"
            @click="openImageViewer('/storage/' + row.pod_signature_path, 'Tanda Tangan Penerima', row.pod_recipient_name)"
            title="Lihat Tanda Tangan"
          >
            <i class="bi bi-pen fs-5"></i>
          </button>
          
          <span v-if="!row.pod_photo_path && !row.pod_signature_path" class="text-muted">-</span>
        </div>
      </template>
    </DataTable>
    <!-- WhatsApp-Style Full View Image Modal -->
    <Teleport to="body">
      <div 
        v-if="showImageModal" 
        class="whatsapp-image-viewer d-flex flex-column align-items-center justify-content-center"
        @click.self="closeImageViewer"
      >
        <!-- Top bar with title, recipient, and close X button -->
        <div class="viewer-header w-100 d-flex justify-content-between align-items-center px-4 py-3 text-white">
          <div>
            <h6 class="fw-bold mb-0 text-white">{{ imageModalTitle }}</h6>
            <small class="text-muted-custom" style="color: #cbd5e1;">Penerima: <strong class="text-white">{{ imageModalRecipient || '-' }}</strong></small>
          </div>
          <button type="button" class="btn-close-custom text-white bg-transparent border-0 fs-3 p-2" @click="closeImageViewer" title="Tutup">
            <i class="bi bi-x-lg text-white"></i>
          </button>
        </div>

        <!-- Main Image Container -->
        <div class="viewer-body flex-grow-1 d-flex align-items-center justify-content-center w-100 p-3">
          <img 
            :src="imageModalUrl" 
            class="img-fluid whatsapp-img"
            alt="Bukti Pengiriman"
          />
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import DataTable from '../components/DataTable.vue';

const toast = useToast();
const loading = ref(false);
const orders = ref([]);

// Image Viewer Modal state
const showImageModal = ref(false);
const imageModalUrl = ref('');
const imageModalTitle = ref('');
const imageModalRecipient = ref('');

const openImageViewer = (url, title, recipient) => {
  imageModalUrl.value = url;
  imageModalTitle.value = title;
  imageModalRecipient.value = recipient;
  showImageModal.value = true;
};

const closeImageViewer = () => {
  showImageModal.value = false;
  imageModalUrl.value = '';
  imageModalTitle.value = '';
  imageModalRecipient.value = '';
};

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'order_number', header: 'Order Number' },
  { accessorKey: 'job_number', header: 'Job Number' },
  { accessorKey: 'customer.customer_name', header: 'Customer' },
  { accessorKey: 'order_date', header: 'Tgl Order' },
  { accessorKey: 'origin', header: 'Origin' },
  { accessorKey: 'destination', header: 'Destination' },
  { accessorKey: 'driver', header: 'Driver' },
  { accessorKey: 'vehicle', header: 'Armada' },
  { accessorKey: 'status', header: 'Status' },
  { accessorKey: 'pod_info', header: 'Bukti POD' }
];

onMounted(() => {
  fetchReportData();
});

const fetchReportData = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/shipment-orders');
    if (response.data.success) {
      orders.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data laporan TMS.');
  } finally {
    loading.value = false;
  }
};

const exportToExcel = async () => {
  try {
    const response = await axios.get('/shipment-orders/export', {
      responseType: 'blob'
    });
    
    const blob = new Blob([response.data], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    
    const contentDisposition = response.headers['content-disposition'];
    let filename = `Laporan_TMS_${new Date().toISOString().slice(0, 10)}.xlsx`;
    if (contentDisposition) {
      const filenameMatch = contentDisposition.match(/filename="(.+?)"/);
      if (filenameMatch && filenameMatch[1]) {
        filename = filenameMatch[1];
      }
    }
    
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error(err);
    toast.error('Gagal mendownload laporan Excel.');
  }
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-';
  const options = { year: '2-digit', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

const getStatusBadgeClass = (status) => {
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
.xx-small {
  font-size: 0.72rem;
}

/* WhatsApp-Style Image Viewer */
.whatsapp-image-viewer {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.95);
  z-index: 99999;
}

.viewer-header {
  position: absolute;
  top: 0;
  left: 0;
  background: linear-gradient(to bottom, rgba(0,0,0,0.85), rgba(0,0,0,0));
  z-index: 10;
}

.btn-close-custom {
  cursor: pointer;
  opacity: 0.8;
  transition: opacity 0.15s ease-in-out;
}

.btn-close-custom:hover {
  opacity: 1;
}

.whatsapp-img {
  max-width: 90vw;
  max-height: 82vh;
  object-fit: contain;
  box-shadow: 0 10px 35px rgba(0,0,0,0.6);
  border-radius: 8px;
  animation: zoom-in-effect 0.25s ease-out;
}

@keyframes zoom-in-effect {
  from {
    transform: scale(0.92);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
