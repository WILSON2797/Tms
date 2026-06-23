<template>
  <div class="shipment-order-form">
    <div class="mb-4">
      <router-link :to="{ name: 'ShipmentOrders' }" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
      </router-link>
      <h3 class="fw-bold text-white mb-1">{{ isEdit ? 'Edit Shipment Order' : 'Buat Shipment Order Baru' }}</h3>
      <p class="text-muted">{{ isEdit ? 'Perbarui data shipment order.' : 'Masukkan data customer, rute, penerima, dan tanggal estimasi untuk order baru.' }}</p>
    </div>

    <!-- Page Loading Skeleton / Spinner State -->
    <div v-if="pageLoading" class="card bg-dark-card border-card p-5 text-center my-4 d-flex flex-column align-items-center justify-content-center">
      <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
      <h5 class="text-white fw-bold">Memuat Data...</h5>
      <p class="text-muted small mb-0">Sedang mengambil data order dan referensi master dari server.</p>
    </div>

    <div v-else class="card bg-dark-card border-card p-4">
      <form @submit.prevent="handleSubmit">
        
        <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary-custom">Informasi Order Utama</h5>
        <div class="row g-3 mb-4">
          <!-- Job Number (Read Only - Only shown in edit mode) -->
          <div class="col-12 col-md-3" v-if="isEdit">
            <label class="form-label text-muted small mb-1">JOB NUMBER (AUTO-GENERATE)</label>
            <input type="text" :value="jobNumber" class="form-control bg-dark-custom text-info border-secondary-custom fw-bold" readonly disabled />
          </div>

          <!-- Nomor Order (Shipment Customer) -->
          <div class="col-12" :class="isEdit ? 'col-md-3' : 'col-md-4'">
            <label class="form-label text-muted small mb-1">NOMOR ORDER / SHIPMENT CUSTOMER <span class="text-danger">*</span></label>
            <input type="text" v-model="form.order_number" class="form-control bg-dark-custom text-white border-secondary-custom fw-bold" placeholder="E.g. PO-8712398" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Customer Selection -->
          <div class="col-12" :class="isEdit ? 'col-md-3' : 'col-md-4'">
            <label class="form-label text-muted small mb-1">CUSTOMER <span class="text-danger">*</span></label>
            <select v-model="form.customer_id" class="form-select bg-dark-custom text-white border-secondary-custom" required :disabled="isEdit && form.status !== 'DRAFT'">
              <option value="">Pilih Customer...</option>
              <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_name }} ({{ c.customer_code }})</option>
            </select>
          </div>

          <!-- Order Date -->
          <div class="col-12" :class="isEdit ? 'col-md-3' : 'col-md-4'">
            <label class="form-label text-muted small mb-1">TANGGAL ORDER <span class="text-danger">*</span></label>
            <input type="date" v-model="form.order_date" class="form-control bg-dark-custom text-white border-secondary-custom" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>
        </div>

        <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary-custom">Rute Pengiriman</h5>
        <div class="row g-3 mb-4">
          <!-- Origin City -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">KOTA ASAL <span class="text-danger">*</span></label>
            <input type="text" v-model="form.origin_city" class="form-control bg-dark-custom text-white border-secondary-custom" placeholder="E.g. Karawang" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Destination City -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">KOTA TUJUAN <span class="text-danger">*</span></label>
            <input type="text" v-model="form.destination_city" class="form-control bg-dark-custom text-white border-secondary-custom" placeholder="E.g. Jakarta" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Detail Address (Destination Detail) -->
          <div class="col-12">
            <label class="form-label text-muted small mb-1">DETAIL ALAMAT TUJUAN <span class="text-danger">*</span></label>
            <textarea v-model="form.detail_address" class="form-control bg-dark-custom text-white border-secondary-custom" rows="2" placeholder="Alamat lengkap dealer/tujuan pengiriman..." required :disabled="isEdit && form.status !== 'DRAFT'"></textarea>
          </div>
        </div>

        <h5 class="fw-bold text-white mb-3 pb-2 border-bottom border-secondary-custom">Informasi Penerima & Jadwal</h5>
        <div class="row g-3 mb-4">
          <!-- Recipient Name -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">NAMA PENERIMA <span class="text-danger">*</span></label>
            <input type="text" v-model="form.recipient_name" class="form-control bg-dark-custom text-white border-secondary-custom" placeholder="Nama PIC Penerima" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Recipient Company -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">PERUSAHAAN PENERIMA (COMPANY / DEALER) <span class="text-danger">*</span></label>
            <input type="text" v-model="form.recipient_company" class="form-control bg-dark-custom text-white border-secondary-custom" placeholder="Nama Dealer / Perusahaan" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Expected Delivery Date -->
          <div class="col-12 col-md-4">
            <label class="form-label text-muted small mb-1">ESTIMASI TANGGAL PENGIRIMAN <span class="text-danger">*</span></label>
            <input type="date" v-model="form.expected_delivery_date" class="form-control bg-dark-custom text-white border-secondary-custom" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Order Type (REGULAR / URGENT) -->
          <div class="col-12 col-md-4">
            <label class="form-label text-muted small mb-1">TIPE ORDER <span class="text-danger">*</span></label>
            <select v-model="form.order_type" class="form-select bg-dark-custom text-white border-secondary-custom" required :disabled="isEdit && form.status !== 'DRAFT'">
              <option value="REGULAR">REGULAR</option>
              <option value="URGENT">URGENT</option>
            </select>
          </div>

          <!-- Transporter Vendor -->
          <div class="col-12 col-md-4">
            <label class="form-label text-muted small mb-1">TRANSPORTER (VENDOR EXPIRED / OPSIONAL)</label>
            <select v-model="form.transporter_id" class="form-select bg-dark-custom text-white border-secondary-custom">
              <option value="">Pilih Transporter...</option>
              <option v-for="t in transporters" :key="t.id" :value="t.id">{{ t.transporter_name }}</option>
            </select>
          </div>

          <!-- Status selection (if editing) -->
          <div class="col-12 col-md-4" v-if="isEdit">
            <label class="form-label text-muted small mb-1">STATUS ORDER <span class="text-danger">*</span></label>
            <select v-model="form.status" class="form-select bg-dark-custom text-white border-secondary-custom" required>
              <option value="DRAFT">DRAFT</option>
              <option value="ASSIGNED">ASSIGNED</option>
              <option value="IN_TRANSIT">IN TRANSIT</option>
              <option value="DELIVERED">DELIVERED</option>
              <option value="CANCELLED">CANCELLED</option>
            </select>
          </div>
        </div>

        <!-- Form Buttons -->
        <div class="d-flex align-items-center justify-content-end gap-3">
          <router-link :to="{ name: 'ShipmentOrders' }" class="btn btn-secondary px-4">Tutup / Batal</router-link>
          <button type="submit" class="btn btn-primary px-4" :disabled="submitLoading">
            <span v-if="submitLoading" class="spinner-border spinner-border-sm me-2"></span>
            Simpan Order
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const route = useRoute();
const router = useRouter();

const isEdit = ref(false);
const pageLoading = ref(true);
const submitLoading = ref(false);
const jobNumber = ref('');

const customers = ref([]);
const transporters = ref([]);

const form = reactive({
  customer_id: '',
  order_date: new Date().toISOString().split('T')[0],
  order_number: '',
  origin_city: '',
  destination_city: '',
  detail_address: '',
  transporter_id: '',
  recipient_name: '',
  recipient_company: '',
  expected_delivery_date: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Default tomorrow
  order_type: 'REGULAR',
  status: 'DRAFT'
});

onMounted(async () => {
  try {
    await fetchMasterData();
    
    if (route.params.id) {
      isEdit.value = true;
      await fetchOrderDetails(route.params.id);
    }
  } catch (err) {
    toast.error('Gagal memuat data halaman.');
  } finally {
    pageLoading.value = false;
  }
});

const fetchMasterData = async () => {
  try {
    const [custRes, trspRes] = await Promise.all([
      axios.get('/customers'),
      axios.get('/transporters')
    ]);

    customers.value = custRes.data.data.filter(c => c.is_active);
    transporters.value = trspRes.data.data.filter(t => t.is_active);
  } catch (err) {
    toast.error('Gagal mengambil data referensi master.');
  }
};

const fetchOrderDetails = async (id) => {
  try {
    const response = await axios.get(`/shipment-orders/${id}`);
    if (response.data.success) {
      const data = response.data.data;
      jobNumber.value = data.job_number;
      form.customer_id = data.customer_id;
      form.order_date = data.order_date;
      form.order_number = data.order_number;
      form.origin_city = data.origin_city;
      form.destination_city = data.destination_city;
      form.detail_address = data.detail_address;
      form.transporter_id = data.transporter_id || '';
      form.recipient_name = data.recipient_name;
      form.recipient_company = data.recipient_company;
      form.expected_delivery_date = data.expected_delivery_date;
      form.order_type = data.order_type;
      form.status = data.status;
    }
  } catch (err) {
    toast.error('Gagal memuat detail shipment order.');
  }
};

const handleSubmit = async () => {
  const payload = {
    ...form,
    transporter_id: form.transporter_id || null,
  };

  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/shipment-orders/${route.params.id}`, payload);
    } else {
      response = await axios.post('/shipment-orders', payload);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      router.push({ name: 'ShipmentOrders' });
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan shipment order.');
  } finally {
    submitLoading.value = false;
  }
};
</script>

<style scoped>
.shipment-order-form {
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

.form-control:focus, .form-select:focus {
  background-color: rgba(10, 15, 26, 0.8) !important;
  border-color: #0d6efd !important;
  color: #fff !important;
  box-shadow: 0 0 10px rgba(13, 110, 253, 0.25) !important;
}

.text-muted {
  color: #8c98a5 !important;
}
</style>
