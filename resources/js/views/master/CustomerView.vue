<template>
  <div class="customer-view">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h3 class="fw-bold text-white mb-1">Customer Management</h3>
        <p class="text-muted">Kelola data pelanggan, alamat, PIC, dan informasi kontak.</p>
      </div>
      <button 
        v-if="authStore.hasPermission('create-master')" 
        class="btn btn-primary d-flex align-items-center gap-2"
        @click="openAddModal"
      >
        <i class="bi bi-plus-lg"></i>
        <span>Tambah Customer</span>
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
              placeholder="Cari customer..."
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="filteredCustomers" 
      :loading="loading" 
      empty-text="Belum ada data customer."
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

    <!-- Modal Form (Bootstrap Modal) -->
    <div 
      class="modal fade" 
      id="customerModal" 
      tabindex="-1" 
      aria-labelledby="customerModalLabel" 
      aria-hidden="true"
      ref="modalRef"
    >
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark-card border-card text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="customerModalLabel">
              {{ isEdit ? 'Edit Customer' : 'Tambah Customer' }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KODE CUSTOMER</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.customer_code" 
                    placeholder="E.g. CUST-01" 
                    required 
                    :disabled="isEdit"
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NAMA CUSTOMER</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.customer_name" 
                    placeholder="Nama Perusahaan" 
                    required 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">ALAMAT</label>
                  <textarea 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.address" 
                    placeholder="Alamat kantor..." 
                    rows="3"
                  ></textarea>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KOTA</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.city" 
                    placeholder="Jakarta" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NAMA PIC</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-white border-secondary-custom" 
                    v-model="form.pic_name" 
                    placeholder="PIC Hubungan" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">TELEPON</label>
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
                    placeholder="office@mail.com" 
                  />
                </div>
                <div class="col-12">
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

// Modal helper
import { Modal } from 'bootstrap';

const authStore = useAuthStore();
const toast = useToast();
const loading = ref(false);
const submitLoading = ref(false);
const customers = ref([]);
const searchQuery = ref('');
const modalRef = ref(null);
let bootstrapModal = null;

const isEdit = ref(false);
const currentId = ref(null);

const form = reactive({
  customer_code: '',
  customer_name: '',
  address: '',
  city: '',
  pic_name: '',
  phone: '',
  email: '',
  is_active: true
});

// TanStack Table Columns definition
const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'customer_code', header: 'Kode' },
  { accessorKey: 'customer_name', header: 'Nama' },
  { accessorKey: 'city', header: 'Kota' },
  { accessorKey: 'pic_name', header: 'PIC' },
  { accessorKey: 'phone', header: 'Telepon' },
  { accessorKey: 'is_active', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

// Computed data filtered by search query
const filteredCustomers = computed(() => {
  if (!searchQuery.value) return customers.value;
  const query = searchQuery.value.toLowerCase();
  return customers.value.filter(c => 
    c.customer_name.toLowerCase().includes(query) ||
    c.customer_code.toLowerCase().includes(query) ||
    (c.city && c.city.toLowerCase().includes(query)) ||
    (c.pic_name && c.pic_name.toLowerCase().includes(query))
  );
});

onMounted(() => {
  bootstrapModal = new Modal(modalRef.value);
  fetchCustomers();
});

const fetchCustomers = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/customers');
    if (response.data.success) {
      customers.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data customer.');
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEdit.value = false;
  currentId.value = null;
  form.customer_code = '';
  form.customer_name = '';
  form.address = '';
  form.city = '';
  form.pic_name = '';
  form.phone = '';
  form.email = '';
  form.is_active = true;
  bootstrapModal.show();
};

const openEditModal = (customer) => {
  isEdit.value = true;
  currentId.value = customer.id;
  form.customer_code = customer.customer_code;
  form.customer_name = customer.customer_name;
  form.address = customer.address;
  form.city = customer.city;
  form.pic_name = customer.pic_name;
  form.phone = customer.phone;
  form.email = customer.email;
  form.is_active = customer.is_active;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/customers/${currentId.value}`, form);
    } else {
      response = await axios.post('/customers', form);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      bootstrapModal.hide();
      fetchCustomers();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = async (customer) => {
  const confirmDelete = confirm(`Apakah Anda yakin ingin menghapus customer "${customer.customer_name}"?`);
  if (!confirmDelete) return;

  try {
    const response = await axios.delete(`/customers/${customer.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchCustomers();
    }
  } catch (err) {
    toast.error('Gagal menghapus customer.');
  }
};
</script>

<style scoped>
.customer-view {
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
