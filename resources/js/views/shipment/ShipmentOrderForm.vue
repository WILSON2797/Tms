<template>
  <div class="shipment-order-form">
    <div class="mb-4">
      <router-link :to="{ name: 'ShipmentOrders' }" class="btn btn-sm btn-outline-secondary mb-2">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
      </router-link>
      <h3 class="fw-bold text-gray-900 mb-1">{{ isEdit ? 'Edit Shipment Order' : 'Buat Shipment Order Baru' }}</h3>
      <p class="text-muted">{{ isEdit ? 'Perbarui data shipment order.' : 'Masukkan data customer, rute, penerima, dan tanggal estimasi untuk order baru.' }}</p>
    </div>

    <!-- Page Loading Skeleton / Spinner State -->
    <div v-if="pageLoading"
      class="card bg-dark-card border-card p-5 text-center my-4 d-flex flex-column align-items-center justify-content-center">
      <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
      <h5 class="text-gray-900 fw-bold">Memuat Data...</h5>
      <p class="text-muted small mb-0">Sedang mengambil data order dan referensi master dari server.</p>
    </div>

    <div v-else class="card bg-dark-card border-card p-4">
      <form @submit.prevent="handleSubmit">

        <h5 class="fw-bold text-gray-900 mb-3 pb-2 border-bottom border-secondary-custom">Informasi Order Utama</h5>
        <div class="row g-3 mb-4">
          <!-- Job Number (Read Only - Only shown in edit mode) -->
          <div class="col-12 col-md-3" v-if="isEdit">
            <label class="form-label text-muted small mb-1">JOB NUMBER (AUTO-GENERATE)</label>
            <input type="text" :value="jobNumber"
              class="form-control bg-dark-custom text-info border-secondary-custom fw-bold" readonly disabled />
          </div>

          <!-- Nomor Order (Shipment Customer) -->
          <div class="col-12" :class="isEdit ? 'col-md-3' : 'col-md-4'">
            <label class="form-label text-muted small mb-1">NOMOR ORDER / SHIPMENT CUSTOMER <span
                class="text-danger">*</span></label>
            <input type="text" v-model="form.order_number"
              class="form-control bg-dark-custom text-gray-900 border-secondary-custom fw-bold"
              placeholder="E.g. PO-8712398" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Customer Selection -->
          <div class="col-12" :class="isEdit ? 'col-md-3' : 'col-md-4'">
            <label class="form-label text-muted small mb-1">CUSTOMER <span class="text-danger">*</span></label>
            <v-select v-model="form.customer_id" :options="customers" label="label" :reduce="c => c.id"
              placeholder="Pilih Customer..." :clearable="false" :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Order Date -->
          <div class="col-12" :class="isEdit ? 'col-md-3' : 'col-md-4'">
            <label class="form-label text-muted small mb-1">TANGGAL ORDER <span class="text-danger">*</span></label>
            <VueDatePicker v-model="form.order_date" model-type="yyyy-MM-dd" :enable-time-picker="false"
              placeholder="Pilih Tanggal Order..." :clearable="false" auto-position="bottom"
              :disabled="isEdit && form.status !== 'DRAFT'" auto-apply format="dd/MM/yyyy" />
          </div>
        </div>

        <h5 class="fw-bold text-gray-900 mb-3 pb-2 border-bottom border-secondary-custom">Rute Pengiriman</h5>
        
        <!-- Rute Asal (Origin) -->
        <h6 class="text-gray-900 small fw-bold mb-2"><i class="bi bi-geo-alt-fill text-primary"></i> Wilayah Asal (Origin)</h6>
        <div class="row g-3 mb-3">
          <!-- Origin Province -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">PROVINSI ASAL <span class="text-danger">*</span></label>
            <v-select v-model="form.origin_province" :options="provinces"
              placeholder="Pilih Provinsi Asal..." :clearable="false" :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Origin City / Kec -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">KOTA/KEC ASAL <span class="text-danger">*</span></label>
            <v-select taggable push-tags v-model="form.origin_city" :options="filteredOriginCities"
              placeholder="Pilih atau ketik Kota/Kec Asal..." :clearable="false" :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>
        </div>

        <!-- Rute Tujuan (Destination) -->
        <h6 class="text-gray-900 small fw-bold mb-2 mt-3"><i class="bi bi-geo-fill text-success"></i> Wilayah Tujuan (Destination)</h6>
        <div class="row g-3 mb-4">
          <!-- Destination Province -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">PROVINSI TUJUAN <span class="text-danger">*</span></label>
            <v-select v-model="form.destination_province" :options="provinces"
              placeholder="Pilih Provinsi Tujuan..." :clearable="false" :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Destination City / Kec -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">KOTA/KEC TUJUAN <span class="text-danger">*</span></label>
            <v-select taggable push-tags v-model="form.destination_city" :options="filteredDestCities"
              placeholder="Pilih atau ketik Kota/Kec Tujuan..." :clearable="false" :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Detail Address (Destination Detail) -->
          <div class="col-12">
            <label class="form-label text-muted small mb-1">DETAIL ALAMAT TUJUAN <span
                class="text-danger">*</span></label>
            <textarea v-model="form.detail_address"
              class="form-control bg-dark-custom text-gray-900 border-secondary-custom" rows="2"
              placeholder="Alamat lengkap dealer/tujuan pengiriman..." required
              :disabled="isEdit && form.status !== 'DRAFT'"></textarea>
          </div>
        </div>

        <h5 class="fw-bold text-gray-900 mb-3 pb-2 border-bottom border-secondary-custom">Informasi Penerima & Jadwal
        </h5>
        <div class="row g-3 mb-4">
          <!-- Recipient Name -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">NAMA PENERIMA <span class="text-danger">*</span></label>
            <input type="text" v-model="form.recipient_name"
              class="form-control bg-dark-custom text-gray-900 border-secondary-custom" placeholder="Nama PIC Penerima"
              required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Recipient Company -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small mb-1">PERUSAHAAN PENERIMA (COMPANY / DEALER) <span
                class="text-danger">*</span></label>
            <input type="text" v-model="form.recipient_company"
              class="form-control bg-dark-custom text-gray-900 border-secondary-custom"
              placeholder="Nama Dealer / Perusahaan" required :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>

          <!-- Expected Delivery Date -->
          <div class="col-12 col-md-4">
            <label class="form-label text-muted small mb-1">ESTIMASI TANGGAL PENGIRIMAN <span
                class="text-danger">*</span></label>
            <VueDatePicker v-model="form.expected_delivery_date" model-type="yyyy-MM-dd" :enable-time-picker="false"
              placeholder="Pilih Estimasi Tanggal..." :clearable="false" auto-position="bottom"
              :disabled="isEdit && form.status !== 'DRAFT'" auto-apply format="dd/MM/yyyy" />
          </div>

          <!-- Order Type (REGULAR / URGENT) -->
          <div class="col-12 col-md-4">
            <label class="form-label text-muted small mb-1">TIPE ORDER <span class="text-danger">*</span></label>
            <v-select v-model="form.order_type" :options="['REGULAR', 'URGENT']" placeholder="Pilih Tipe Order..."
              :clearable="false" :disabled="isEdit && form.status !== 'DRAFT'" />
          </div>



          <!-- Status selection (if editing) -->
          <div class="col-12 col-md-4" v-if="isEdit">
            <label class="form-label text-muted small mb-1">STATUS ORDER <span class="text-danger">*</span></label>
            <v-select v-model="form.status" :options="['DRAFT', 'ASSIGNED', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED']"
              placeholder="Pilih Status..." :clearable="false" />
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
import { ref, reactive, onMounted, computed } from 'vue';
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
const cities = ref([]);

const form = reactive({
  customer_id: '',
  order_date: new Date().toISOString().split('T')[0],
  order_number: '',
  origin_city: '',
  origin_province: '',
  destination_city: '',
  destination_province: '',
  detail_address: '',

  recipient_name: '',
  recipient_company: '',
  expected_delivery_date: new Date(Date.now() + 86400000).toISOString().split('T')[0], // Default tomorrow
  order_type: 'REGULAR',
  status: 'DRAFT'
});

// Computed properties for structured location dropdowns
const provinces = computed(() => {
  if (!cities.value || cities.value.length === 0) return [];
  const list = cities.value.map(c => c.province);
  return [...new Set(list)].sort();
});

const filteredOriginCities = computed(() => {
  if (!form.origin_province) return [];
  return cities.value
    .filter(c => c.province.toUpperCase() === form.origin_province.toUpperCase())
    .map(c => c.name);
});

const filteredDestCities = computed(() => {
  if (!form.destination_province) return [];
  return cities.value
    .filter(c => c.province.toUpperCase() === form.destination_province.toUpperCase())
    .map(c => c.name);
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
    const [custRes, citiesRes] = await Promise.all([
      axios.get('/customers'),
      axios.get('/cities')
    ]);

    customers.value = custRes.data.data.filter(c => c.is_active).map(c => ({
      ...c,
      label: `${c.customer_name} (${c.customer_code})`
    }));

    cities.value = citiesRes.data.data.map(c => ({
      ...c,
      label: `${c.type} ${c.name} (${c.province})`
    }));
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
      form.origin_province = data.origin_province || '';
      form.destination_city = data.destination_city;
      form.destination_province = data.destination_province || '';
      form.detail_address = data.detail_address;

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
/* Scoped overrides removed to use global clean light-mode theme */
</style>
