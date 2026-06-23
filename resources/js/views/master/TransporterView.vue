<template>
  <div class="transporter-view">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h3 class="fw-bold text-white mb-1">Transporter Management</h3>
        <p class="text-muted">Kelola data vendor pengiriman / transporter pihak ketiga beserta PIC dan nomor kontak.</p>
      </div>
      <button 
        v-if="authStore.hasPermission('create-master')" 
        class="btn btn-primary d-flex align-items-center gap-2"
        @click="openAddModal"
      >
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Transporter</span>
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
              placeholder="Cari transporter..."
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="filteredTransporters" 
      :loading="loading" 
      empty-text="Belum ada data transporter."
    >
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
      id="transporterModal" 
      tabindex="-1" 
      aria-labelledby="transporterModalLabel" 
      aria-hidden="true"
      ref="modalRef"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark-card border-card text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="transporterModalLabel">
              {{ isEdit ? 'Edit Transporter' : 'Tambah Transporter' }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KODE TRANSPORTER</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.transporter_code" 
                    placeholder="E.g. TRSP-01" 
                    required 
                    :disabled="isEdit"
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NAMA TRANSPORTER</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.transporter_name" 
                    placeholder="Nama perusahaan transporter" 
                    required 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">ALAMAT</label>
                  <textarea 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.address" 
                    placeholder="Alamat kantor transporter..." 
                    rows="3"
                  ></textarea>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NAMA PIC</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.pic_name" 
                    placeholder="Nama penanggung jawab" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NO TELEPON</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.phone" 
                    placeholder="0812xxxxxx" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">EMAIL</label>
                  <input 
                    type="email" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.email" 
                    placeholder="contact@transporter.com" 
                  />
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
const transporters = ref([]);
const searchQuery = ref('');
const modalRef = ref(null);
let bootstrapModal = null;

const isEdit = ref(false);
const currentId = ref(null);

const form = reactive({
  transporter_code: '',
  transporter_name: '',
  address: '',
  pic_name: '',
  phone: '',
  email: '',
  is_active: true
});

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'transporter_code', header: 'Kode' },
  { accessorKey: 'transporter_name', header: 'Nama' },
  { accessorKey: 'pic_name', header: 'PIC' },
  { accessorKey: 'phone', header: 'Telepon' },
  { accessorKey: 'email', header: 'Email' },
  { accessorKey: 'is_active', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

const filteredTransporters = computed(() => {
  if (!searchQuery.value) return transporters.value;
  const query = searchQuery.value.toLowerCase();
  return transporters.value.filter(t => 
    t.transporter_name.toLowerCase().includes(query) ||
    t.transporter_code.toLowerCase().includes(query) ||
    (t.pic_name && t.pic_name.toLowerCase().includes(query))
  );
});

onMounted(() => {
  bootstrapModal = new Modal(modalRef.value);
  fetchTransporters();
});

const fetchTransporters = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/transporters');
    if (response.data.success) {
      transporters.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data transporter.');
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEdit.value = false;
  currentId.value = null;
  form.transporter_code = '';
  form.transporter_name = '';
  form.address = '';
  form.pic_name = '';
  form.phone = '';
  form.email = '';
  form.is_active = true;
  bootstrapModal.show();
};

const openEditModal = (transporter) => {
  isEdit.value = true;
  currentId.value = transporter.id;
  form.transporter_code = transporter.transporter_code;
  form.transporter_name = transporter.transporter_name;
  form.address = transporter.address;
  form.pic_name = transporter.pic_name;
  form.phone = transporter.phone;
  form.email = transporter.email;
  form.is_active = transporter.is_active;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/transporters/${currentId.value}`, form);
    } else {
      response = await axios.post('/transporters', form);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      bootstrapModal.hide();
      fetchTransporters();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = async (transporter) => {
  const confirmDelete = confirm(`Apakah Anda yakin ingin menghapus transporter "${transporter.transporter_name}"?`);
  if (!confirmDelete) return;

  try {
    const response = await axios.delete(`/transporters/${transporter.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchTransporters();
    }
  } catch (err) {
    toast.error('Gagal menghapus transporter.');
  }
};
</script>

<style scoped>
.transporter-view {
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
