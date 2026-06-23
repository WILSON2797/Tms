<template>
  <div class="driver-view">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h3 class="fw-bold text-white mb-1">Driver Management</h3>
        <p class="text-muted">Kelola data driver, SIM, tanggal kadaluarsa, dan unggah foto profil.</p>
      </div>
      <button 
        v-if="authStore.hasPermission('create-master')" 
        class="btn btn-primary d-flex align-items-center gap-2"
        @click="openAddModal"
      >
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Driver</span>
      </button>
    </div>

    <!-- Filter & Search Panel -->
    <div class="card bg-dark-card border-card p-3 mb-4">
      <div class="row g-3 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group">
            <span class="input-group-text bg-dark-custom text-muted-custom border-secondary-custom">
              <i class="bi bi-search"></i>
            </span>
            <input
              type="text"
              class="form-control bg-dark-custom text-white border-secondary-custom"
              v-model="searchQuery"
              placeholder="Cari driver..."
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="filteredDrivers" 
      :loading="loading" 
      empty-text="Belum ada data driver."
    >
      <template #cell(photo)="{ value }">
        <img 
          :src="value || 'https://via.placeholder.com/40'" 
          alt="Driver" 
          class="rounded-circle object-fit-cover" 
          width="40" 
          height="40"
        />
      </template>
      <template #cell(is_active)="{ value }">
        <span class="badge" :class="value ? 'bg-success' : 'bg-danger'">
          {{ value ? 'Aktif' : 'Non-Aktif' }}
        </span>
      </template>
      <template #cell(actions)="{ row }">
        <div class="d-flex gap-2">
          <button 
            v-if="authStore.hasPermission('edit-master')" 
            class="btn btn-sm btn-outline-info border-0" 
            @click="openEditModal(row)"
          >
            <i class="bi bi-pencil-square"></i>
          </button>
          <button 
            v-if="authStore.hasPermission('delete-master')" 
            class="btn btn-sm btn-outline-danger border-0" 
            @click="handleDelete(row)"
          >
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Modal Form -->
    <div 
      class="modal fade" 
      id="driverModal" 
      tabindex="-1" 
      aria-labelledby="driverModalLabel" 
      aria-hidden="true"
      ref="modalRef"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark-card border-card text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="driverModalLabel">
              {{ isEdit ? 'Edit Driver' : 'Tambah Driver' }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KODE DRIVER</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.driver_code" 
                    placeholder="E.g. DRV-01" 
                    required 
                    :disabled="isEdit"
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NAMA LENGKAP</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.driver_name" 
                    placeholder="Nama lengkap driver" 
                    required 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NO TELEPON</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.phone" 
                    placeholder="0812xxxx" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NO SIM</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.sim_number" 
                    placeholder="No SIM A/B1/B2" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">SIM KADALUARSA</label>
                  <input 
                    type="date" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.sim_expired" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">TIPE LISENSI (GOLONGAN SIM)</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.license_type" 
                    placeholder="SIM A / SIM B1 / SIM B2" 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">ALAMAT</label>
                  <textarea 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.address" 
                    placeholder="Alamat lengkap..." 
                    rows="3"
                  ></textarea>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">FOTO DRIVER</label>
                  <input 
                    type="file" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    @change="handleFileChange" 
                    accept="image/*"
                  />
                  <small class="text-muted d-block mt-1">Ukuran maksimal 2MB (format gambar)</small>
                </div>
                <div class="col-12 col-md-6 d-flex align-items-center mt-auto">
                  <div class="form-check form-switch">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      id="isActiveSwitch" 
                      v-model="form.is_active" 
                    />
                    <label class="form-check-label text-light" for="isActiveSwitch">Status Aktif</label>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer border-secondary-custom">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="submitLoading">
                <span v-if="submitLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import DataTable from '../../components/DataTable.vue';
import { Modal } from 'bootstrap';

const authStore = useAuthStore();
const toast = useToast();
const loading = ref(false);
const submitLoading = ref(false);
const drivers = ref([]);
const searchQuery = ref('');
const modalRef = ref(null);
let bootstrapModal = null;

const isEdit = ref(false);
const currentId = ref(null);
const photoFile = ref(null);

const form = reactive({
  driver_code: '',
  driver_name: '',
  phone: '',
  sim_number: '',
  sim_expired: '',
  license_type: '',
  address: '',
  is_active: true
});

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'photo', header: 'Foto' },
  { accessorKey: 'driver_code', header: 'Kode' },
  { accessorKey: 'driver_name', header: 'Nama' },
  { accessorKey: 'phone', header: 'Telepon' },
  { accessorKey: 'license_type', header: 'Tipe SIM' },
  { accessorKey: 'is_active', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

const filteredDrivers = computed(() => {
  if (!searchQuery.value) return drivers.value;
  const query = searchQuery.value.toLowerCase();
  return drivers.value.filter(d => 
    d.driver_name.toLowerCase().includes(query) ||
    d.driver_code.toLowerCase().includes(query) ||
    (d.license_type && d.license_type.toLowerCase().includes(query)) ||
    (d.phone && d.phone.toLowerCase().includes(query))
  );
});

onMounted(() => {
  bootstrapModal = new Modal(modalRef.value);
  fetchDrivers();
});

const fetchDrivers = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/drivers');
    if (response.data.success) {
      drivers.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data driver.');
  } finally {
    loading.value = false;
  }
};

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    photoFile.value = file;
  }
};

const openAddModal = () => {
  isEdit.value = false;
  currentId.value = null;
  photoFile.value = null;
  form.driver_code = '';
  form.driver_name = '';
  form.phone = '';
  form.sim_number = '';
  form.sim_expired = '';
  form.license_type = '';
  form.address = '';
  form.is_active = true;
  bootstrapModal.show();
};

const openEditModal = (driver) => {
  isEdit.value = true;
  currentId.value = driver.id;
  photoFile.value = null;
  form.driver_code = driver.driver_code;
  form.driver_name = driver.driver_name;
  form.phone = driver.phone;
  form.sim_number = driver.sim_number;
  form.sim_expired = driver.sim_expired || '';
  form.license_type = driver.license_type;
  form.address = driver.address;
  form.is_active = driver.is_active;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  submitLoading.value = true;
  
  // Use FormData to support image uploads
  const formData = new FormData();
  formData.append('driver_code', form.driver_code);
  formData.append('driver_name', form.driver_name);
  formData.append('phone', form.phone || '');
  formData.append('sim_number', form.sim_number || '');
  formData.append('sim_expired', form.sim_expired || '');
  formData.append('license_type', form.license_type || '');
  formData.append('address', form.address || '');
  formData.append('is_active', form.is_active ? '1' : '0');
  
  if (photoFile.value) {
    formData.append('photo_file', photoFile.value);
  }

  try {
    let response;
    if (isEdit.value) {
      // Direct POST to allow PHP multipart file update
      response = await axios.post(`/drivers/${currentId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } else {
      response = await axios.post('/drivers', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    }

    if (response.data.success) {
      toast.success(response.data.message);
      bootstrapModal.hide();
      fetchDrivers();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = async (driver) => {
  const confirmDelete = confirm(`Apakah Anda yakin ingin menghapus driver "${driver.driver_name}"?`);
  if (!confirmDelete) return;

  try {
    const response = await axios.delete(`/drivers/${driver.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchDrivers();
    }
  } catch (err) {
    toast.error('Gagal menghapus driver.');
  }
};
</script>

<style scoped>
.driver-view {
  background-color: #0b0f19;
}

.text-muted {
  color: #8c98a5 !important;
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

.form-control:focus {
  background-color: rgba(10, 15, 26, 0.8) !important;
  border-color: #0d6efd !important;
  color: #fff !important;
  box-shadow: 0 0 10px rgba(13, 110, 253, 0.25) !important;
}

.table-header-custom {
  background-color: rgba(255, 255, 255, 0.02);
}

.btn-outline-info:hover {
  background-color: rgba(13, 202, 240, 0.1);
  color: #0dcaf0;
}

.btn-outline-danger:hover {
  background-color: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}
</style>
