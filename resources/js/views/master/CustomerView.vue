<template>
  <div class="customer-view">
    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="customers" 
      :loading="loading" 
      empty-text="Belum ada data customer."
      title="Customer Management"
      subtitle="Kelola data pelanggan, alamat, PIC, dan informasi kontak."
    >
      <template #actions>
        <button 
          v-if="authStore.hasPermission('create-master')" 
          class="btn btn-primary d-flex align-items-center gap-2"
          @click="openCreateModal"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Customer</span>
        </button>
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
        <div class="modal-content bg-dark-card border-card text-gray-900 dark:text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="customerModalLabel">
              {{ isEdit ? 'Edit Customer' : 'Tambah Customer' }}
            </h5>
            <button type="button" class="btn-close dark:btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KODE CUSTOMER</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
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
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.customer_name" 
                    placeholder="Nama Perusahaan" 
                    required 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">ALAMAT</label>
                  <textarea 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.address" 
                    placeholder="Alamat kantor..." 
                    rows="3"
                  ></textarea>
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KOTA</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.city" 
                    placeholder="Jakarta" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NAMA PIC</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.pic_name" 
                    placeholder="PIC Hubungan" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">TELEPON</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.phone" 
                    placeholder="0812xxxxxx" 
                  />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">EMAIL</label>
                  <input 
                    type="email" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
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
                    <label class="form-check-label text-gray-700 dark:text-gray-300" for="isActiveSwitch">Status Aktif</label>
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
import Swal from 'sweetalert2';
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
const editingId = ref(null);

const form = reactive({
  customer_code: '',
  customer_name: '',
  phone: '',
  email: '',
  is_active: true
});

// TanStack Table Columns definition
const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'customer_code', header: 'Kode Customer' },
  { accessorKey: 'customer_name', header: 'Nama Customer' },
  { accessorKey: 'phone', header: 'Telepon' },
  { accessorKey: 'email', header: 'Email' },
  { accessorKey: 'is_active', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

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

const openCreateModal = () => {
  isEdit.value = false;
  editingId.value = null;
  form.customer_code = '';
  form.customer_name = '';
  form.phone = '';
  form.email = '';
  form.is_active = true;
  bootstrapModal.show();
};

const openEditModal = (customer) => {
  isEdit.value = true;
  editingId.value = customer.id;
  form.customer_code = customer.customer_code;
  form.customer_name = customer.customer_name;
  form.phone = customer.phone || '';
  form.email = customer.email || '';
  form.is_active = customer.is_active;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/customers/${editingId.value}`, form);
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
  const result = await Swal.fire({
    title: 'Hapus Customer?',
    text: `Apakah Anda yakin ingin menghapus customer "${customer.customer_name}"? Tindakan ini tidak dapat dibatalkan.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });

  if (!result.isConfirmed) return;

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
