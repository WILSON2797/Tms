<template>
  <div class="pod-view">
    <!-- Title and Subtitle manually rendered -->
    <div class="mb-4">
      <h3 class="fw-bold text-gray-900 mb-1" style="font-size: 22px;">Proof of Delivery (POD)</h3>
      <p class="text-muted mb-0 small">Kelola bukti pengiriman (Nama Penerima, Foto Barang, Tanda Tangan Digital) untuk
        menyelesaikan shipment order.</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="tabs-navigation-container mb-4">
      <button @click="statusFilter = 'ACTIVE'" class="tab-card-item" :class="{ 'active': statusFilter === 'ACTIVE' }">
        <i class="bi bi-clock-history me-2"></i>
        Aktif (ASSIGNED / IN_TRANSIT)
      </button>
      <button @click="statusFilter = 'DELIVERED'" class="tab-card-item"
        :class="{ 'active': statusFilter === 'DELIVERED' }">
        <i class="bi bi-check2-all me-2"></i>
        Selesai (DELIVERED)
      </button>
    </div>

    <!-- Data Table -->
    <DataTable :columns="columns" :data="filteredOrders" :loading="loading"
      empty-text="Tidak ada shipment order untuk filter ini.">
      <template #cell(job_number)="{ value }">
        <span class="text-info fw-bold">{{ value }}</span>
      </template>

      <template #cell(status)="{ value }">
        <span class="badge px-2 py-1" :class="getStatusBadgeClass(value)">
          {{ value }}
        </span>
      </template>

      <template #cell(actions)="{ row }">
        <div class="d-flex gap-2">
          <button v-if="row.status === 'ASSIGNED' || row.status === 'IN_TRANSIT'"
            class="btn btn-sm btn-primary d-flex align-items-center gap-1" @click="openPodModal(row)">
            <i class="bi bi-file-earmark-arrow-up"></i>
            <span>Submit POD</span>
          </button>
          <button v-else-if="row.status === 'DELIVERED'"
            class="btn btn-sm btn-outline-success d-flex align-items-center gap-1" @click="viewPodDetails(row)">
            <i class="bi bi-eye"></i>
            <span>Lihat POD</span>
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Modal Submit POD -->
    <Teleport to="body">
      <div v-if="showModal" class="modal-backdrop-custom d-flex align-items-center justify-content-center"
        @click.self="closePodModal">
        <div class="modal-dialog-custom bg-dark-card border-card p-4 rounded-4 shadow-lg text-gray-900">
          <div
            class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary-custom pb-2">
            <h5 class="fw-bold text-gray-900 mb-0">
              <i class="bi bi-file-earmark-check text-primary me-2"></i>
              Submit POD - {{ selectedOrder?.job_number }}
            </h5>
            <button type="button" class="btn-close" @click="closePodModal"></button>
          </div>

          <form @submit.prevent="submitPod">
            <div class="mb-3">
              <label class="form-label text-muted small fw-bold">NAMA PENERIMA</label>
              <input type="text" v-model="podForm.pod_recipient_name"
                class="form-control bg-dark-custom text-gray-900 border-secondary-custom"
                placeholder="Masukkan nama lengkap penerima..." required />
            </div>

            <div class="mb-3">
              <label class="form-label text-muted small fw-bold">FOTO PENYERAHAN BARANG (POD)</label>
              <div
                class="photo-upload-zone border border-dashed border-secondary-custom rounded-3 p-3 text-center bg-dark-custom"
                @click="$refs.photoInput.click()" style="cursor: pointer;">
                <input type="file" ref="photoInput" class="d-none" accept="image/*" capture="environment"
                  @change="handlePhotoUpload" />
                <div v-if="!podForm.pod_photo" class="py-3">
                  <i class="bi bi-camera fs-2 text-muted mb-2 d-block"></i>
                  <span class="text-gray-900 small">Klik untuk Mengambil Foto atau Mengunggah Berkas</span>
                  <p class="text-muted small mb-0 mt-1">Format: JPG, PNG, GIF</p>
                </div>
                <div v-else class="position-relative d-inline-block">
                  <img :src="podForm.pod_photo" class="img-fluid rounded-2 max-h-150" alt="Preview Foto" />
                  <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle"
                    @click.stop="clearPhoto">
                    <i class="bi bi-x"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label text-muted small fw-bold mb-0">TANDA TANGAN DIGITAL</label>
                <button type="button" class="btn btn-link btn-sm text-decoration-none text-danger p-0"
                  @click="clearSignature">
                  <i class="bi bi-eraser me-1"></i>Bersihkan
                </button>
              </div>
              <div class="signature-canvas-container border border-secondary-custom rounded-3 bg-white p-2">
                <canvas ref="canvasRef" class="w-100" height="150" @mousedown="startDrawing" @mousemove="draw"
                  @mouseup="stopDrawing" @mouseleave="stopDrawing" @touchstart="startDrawingTouch"
                  @touchmove="drawTouch" @touchend="stopDrawing"></canvas>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top border-secondary-custom pt-3">
              <button type="button" class="btn btn-secondary" @click="closePodModal">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Kirim Bukti POD
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Modal View Detail POD -->
    <Teleport to="body">
      <div v-if="showViewModal" class="modal-backdrop-custom d-flex align-items-center justify-content-center"
        @click.self="closeViewModal">
        <div class="modal-dialog-custom bg-dark-card border-card p-4 rounded-4 shadow-lg text-gray-900">
          <div
            class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary-custom pb-2">
            <h5 class="fw-bold text-gray-900 mb-0">
              <i class="bi bi-file-earmark-text text-success me-2"></i>
              Detail Proof of Delivery - {{ selectedOrder?.job_number }}
            </h5>
            <button type="button" class="btn-close" @click="closeViewModal"></button>
          </div>

          <div class="row g-3 small mb-3">
            <div class="col-6">
              <span class="text-muted d-block mb-1">NAMA PENERIMA</span>
              <strong class="text-gray-900 fs-6">{{ selectedOrder?.pod_recipient_name }}</strong>
            </div>
            <div class="col-6">
              <span class="text-muted d-block mb-1">TANGGAL DITERIMA</span>
              <strong class="text-gray-900 fs-6">{{ formatDateTime(selectedOrder?.pod_received_at) }}</strong>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-6">
              <span class="text-muted d-block mb-1">FOTO DOKUMENTASI</span>
              <div
                class="pod-image-preview-wrapper border border-secondary-custom rounded bg-white d-flex align-items-center justify-content-center overflow-hidden">
                <img v-if="selectedOrder?.pod_photo_path" :src="`/storage/${selectedOrder.pod_photo_path}`"
                  class="img-fluid" alt="Foto POD" />
                <span v-else class="text-muted">Tidak ada foto</span>
              </div>
            </div>
            <div class="col-6">
              <span class="text-muted d-block mb-1">TANDA TANGAN</span>
              <div
                class="pod-image-preview-wrapper border border-secondary-custom rounded bg-white p-2 d-flex align-items-center justify-content-center overflow-hidden">
                <img v-if="selectedOrder?.pod_signature_path" :src="`/storage/${selectedOrder.pod_signature_path}`"
                  class="img-fluid signature-filter" alt="Tanda Tangan POD" />
                <span v-else class="text-muted">Tidak ada TTD</span>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end border-top border-secondary-custom mt-4 pt-3">
            <button type="button" class="btn btn-secondary" @click="closeViewModal">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import DataTable from '../components/DataTable.vue';

const authStore = useAuthStore();
const toast = useToast();

const loading = ref(false);
const submitting = ref(false);
const orders = ref([]);
const statusFilter = ref('ACTIVE'); // 'ACTIVE' or 'DELIVERED'

const showModal = ref(false);
const showViewModal = ref(false);
const selectedOrder = ref(null);
const photoInput = ref(null);

const podForm = ref({
  pod_recipient_name: '',
  pod_photo: '',
  pod_signature: ''
});

// Canvas Signature Pad references and state
const canvasRef = ref(null);
const isDrawing = ref(false);
let lastX = 0;
let lastY = 0;

const columns = [
  { accessorKey: 'job_number', header: 'Job Number' },
  { accessorKey: 'order_number', header: 'No. Order Customer' },
  { accessorKey: 'customer.customer_name', header: 'Customer' },
  { accessorKey: 'destination_city', header: 'Kota Tujuan' },
  { accessorKey: 'recipient_name', header: 'Penerima Default' },
  { accessorKey: 'status', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

onMounted(() => {
  fetchOrders();
});

const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/shipment-orders');
    if (response.data.success) {
      orders.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal memuat data shipment order.');
  } finally {
    loading.value = false;
  }
};

const filteredOrders = computed(() => {
  if (statusFilter.value === 'ACTIVE') {
    return orders.value.filter(o => o.status === 'ASSIGNED' || o.status === 'IN_TRANSIT');
  } else {
    return orders.value.filter(o => o.status === 'DELIVERED');
  }
});

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'ASSIGNED': return 'bg-primary';
    case 'IN_TRANSIT': return 'bg-warning text-dark';
    case 'DELIVERED': return 'bg-success';
    default: return 'bg-light text-dark';
  }
};

const formatDateTime = (dateStr) => {
  if (!dateStr) return '-';
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateStr).toLocaleDateString('id-ID', options);
};

// POD Modal actions
const openPodModal = (order) => {
  selectedOrder.value = order;
  podForm.value.pod_recipient_name = order.recipient_name || '';
  podForm.value.pod_photo = '';
  podForm.value.pod_signature = '';
  showModal.value = true;

  // Set up signature canvas on next tick once rendered
  nextTick(() => {
    setTimeout(() => {
      initCanvas();
    }, 200);
  });
};

const closePodModal = () => {
  showModal.value = false;
  selectedOrder.value = null;
};

const viewPodDetails = (order) => {
  selectedOrder.value = order;
  showViewModal.value = true;
};

const closeViewModal = () => {
  showViewModal.value = false;
  selectedOrder.value = null;
};

// Photo Upload handlers
const handlePhotoUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    toast.error('Berkas harus berupa gambar.');
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    podForm.value.pod_photo = e.target.result; // base64 string
  };
  reader.readAsDataURL(file);
};

const clearPhoto = () => {
  podForm.value.pod_photo = '';
  if (photoInput.value) {
    photoInput.value.value = '';
  }
};

// Canvas Signature Pad logic
const initCanvas = () => {
  const canvas = canvasRef.value;
  if (!canvas) return;

  // Fit canvas width to parent container width dynamically
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width;

  const ctx = canvas.getContext('2d');
  ctx.strokeStyle = '#0f172a'; // dark stroke
  ctx.lineWidth = 3;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
};

const startDrawing = (e) => {
  isDrawing.value = true;
  const canvas = canvasRef.value;
  const rect = canvas.getBoundingClientRect();
  lastX = e.clientX - rect.left;
  lastY = e.clientY - rect.top;
};

const draw = (e) => {
  if (!isDrawing.value) return;
  const canvas = canvasRef.value;
  const rect = canvas.getBoundingClientRect();
  const x = e.clientX - rect.left;
  const y = e.clientY - rect.top;

  const ctx = canvas.getContext('2d');
  ctx.beginPath();
  ctx.moveTo(lastX, lastY);
  ctx.lineTo(x, y);
  ctx.stroke();

  lastX = x;
  lastY = y;
};

const stopDrawing = () => {
  isDrawing.value = false;
};

// Mobile Touch controls for Canvas
const startDrawingTouch = (e) => {
  e.preventDefault();
  isDrawing.value = true;
  const touch = e.touches[0];
  const canvas = canvasRef.value;
  const rect = canvas.getBoundingClientRect();
  lastX = touch.clientX - rect.left;
  lastY = touch.clientY - rect.top;
};

const drawTouch = (e) => {
  e.preventDefault();
  if (!isDrawing.value) return;
  const touch = e.touches[0];
  const canvas = canvasRef.value;
  const rect = canvas.getBoundingClientRect();
  const x = touch.clientX - rect.left;
  const y = touch.clientY - rect.top;

  const ctx = canvas.getContext('2d');
  ctx.beginPath();
  ctx.moveTo(lastX, lastY);
  ctx.lineTo(x, y);
  ctx.stroke();

  lastX = x;
  lastY = y;
};

const stopDrawingTouch = (e) => {
  e.preventDefault();
  isDrawing.value = false;
};

const clearSignature = () => {
  const canvas = canvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, canvas.width, canvas.height);
};

// Check if signature pad has been drawn on
const isCanvasBlank = () => {
  const canvas = canvasRef.value;
  if (!canvas) return true;

  const blank = document.createElement('canvas');
  blank.width = canvas.width;
  blank.height = canvas.height;
  return canvas.toDataURL() === blank.toDataURL();
};

const submitPod = async () => {
  if (!podForm.value.pod_recipient_name.trim()) {
    toast.warning('Nama penerima harus diisi.');
    return;
  }
  if (!podForm.value.pod_photo) {
    toast.warning('Silakan ambil atau unggah foto penyerahan.');
    return;
  }
  if (isCanvasBlank()) {
    toast.warning('Silakan tandatangani bukti pengiriman.');
    return;
  }

  // Get base64 representation of signature canvas
  podForm.value.pod_signature = canvasRef.value.toDataURL('image/png');

  submitting.value = true;
  try {
    const response = await axios.post(`/shipment-orders/${selectedOrder.value.id}/pod`, {
      pod_recipient_name: podForm.value.pod_recipient_name,
      pod_photo: podForm.value.pod_photo,
      pod_signature: podForm.value.pod_signature
    });

    if (response.data.success) {
      toast.success(response.data.message);
      closePodModal();
      fetchOrders();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal mengirimkan bukti POD.');
  } finally {
    submitting.value = false;
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
  width: 100%;
  max-width: 600px;
  margin: 1.75rem;
  max-height: 90vh;
  overflow-y: auto;
}

.photo-upload-zone {
  border-style: dashed !important;
  transition: border-color 0.2s ease-in-out;
}

.photo-upload-zone:hover {
  border-color: #0d6efd !important;
}

.max-h-150 {
  max-height: 150px;
  object-fit: contain;
}

.signature-canvas-container {
  cursor: crosshair;
}

.pod-image-preview-wrapper {
  height: 140px;
  width: 100%;
}

.pod-image-preview-wrapper img {
  max-height: 100%;
  max-width: 100%;
  object-fit: contain;
}

.signature-filter {
  filter: contrast(130%);
}

/* Custom Tabs Styles */
.tabs-navigation-container {
  display: flex;
  align-items: flex-end;
  /* Align tabs to the bottom */
  gap: 16px;
  border-bottom: 1px solid #e2e8f0;
  /* Clean bottom border line only */
  padding-bottom: 0;
  margin-bottom: 24px;
}

.tab-card-item {
  display: flex;
  align-items: center;
  background: transparent;
  border: 1px solid transparent;
  padding: 10px 16px;
  font-weight: 600;
  font-size: 14.5px;
  color: #64748b;
  /* Slate 500 */
  transition: all 0.15s ease-in-out;
  outline: none;
  cursor: pointer;
  border-top-left-radius: 6px;
  border-top-right-radius: 6px;
  margin-bottom: -1px;
  /* Overlap the bottom border line */
  position: relative;
  z-index: 1;
}

.tab-card-item i {
  color: #94a3b8;
  /* Slate 400 */
  font-size: 16px;
  transition: color 0.15s ease-in-out;
}

.tab-card-item:hover {
  color: #0f172a;
  /* Slate 900 */
}

.tab-card-item:hover i {
  color: #475569;
}

.tab-card-item.active {
  background-color: #ecf3ff;
  /* Soft blue sidebar-like background */
  color: #000000;
  /* Black text */
  border: 1px solid #c3daff;
  border-bottom-color: #ecf3ff;
  /* Cover the bottom line */
  z-index: 2;
}

.tab-card-item.active i {
  color: #000000;
  /* Black icon */
}

/* Dark mode compatibility */
.dark .tabs-navigation-container {
  border-bottom-color: #334155;
}

.dark .tab-card-item {
  color: #94a3b8;
}

.dark .tab-card-item i {
  color: #64748b;
}

.dark .tab-card-item:hover {
  color: #f1f5f9;
}

.dark .tab-card-item.active {
  background-color: rgba(70, 95, 255, 0.15);
  /* Soft brand blue for dark theme */
  color: #ffffff;
  border-color: rgba(70, 95, 255, 0.25);
  border-bottom-color: rgba(70, 95, 255, 0.15);
}

.dark .tab-card-item.active i {
  color: #38bdf8;
}
</style>
