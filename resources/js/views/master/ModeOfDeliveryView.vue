<template>
  <div class="modes-of-delivery-view">
    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="mods" 
      :loading="loading" 
      empty-text="Belum ada data mode of delivery."
      title="Mode of Delivery Management"
      subtitle="Kelola tipe moda pengiriman/penyerahan barang ke customer (misal: FCL, LCL, Express)."
    >
      <template #actions>
        <button 
          v-if="authStore.hasPermission('create-master')" 
          class="btn btn-primary d-flex align-items-center gap-2"
          @click="openAddModal"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Mode of Delivery</span>
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

    <!-- Modal Form -->
    <div 
      class="modal fade" 
      id="modModal" 
      tabindex="-1" 
      aria-labelledby="modModalLabel" 
      aria-hidden="true"
      ref="modalRef"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark-card border-card text-gray-900 dark:text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="modModalLabel">
              {{ isEdit ? 'Edit Mode of Delivery' : 'Tambah Mode of Delivery' }}
            </h5>
            <button type="button" class="btn-close dark:btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">KODE MOD</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.code" 
                    placeholder="E.g. FCL, LCL, EXPRESS" 
                    required 
                    :disabled="isEdit"
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">NAMA MOD</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.name" 
                    placeholder="E.g. Full Container Load" 
                    required 
                  />
                </div>
                <div class="col-12 d-flex align-items-center mt-3">
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
import { ref, reactive, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import DataTable from '../../components/DataTable.vue';
import { Modal } from 'bootstrap';

const authStore = useAuthStore();
const toast = useToast();
const loading = ref(false);
const submitLoading = ref(false);
const mods = ref([]);
const modalRef = ref(null);
let bootstrapModal = null;

const isEdit = ref(false);
const currentId = ref(null);

const form = reactive({
  code: '',
  name: '',
  is_active: true
});

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'code', header: 'Kode MOD' },
  { accessorKey: 'name', header: 'Nama MOD' },
  { accessorKey: 'is_active', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

onMounted(() => {
  bootstrapModal = new Modal(modalRef.value);
  fetchMods();
});

const fetchMods = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/modes-of-delivery/all');
    if (response.data.success) {
      mods.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data mode of delivery.');
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEdit.value = false;
  currentId.value = null;
  form.code = '';
  form.name = '';
  form.is_active = true;
  bootstrapModal.show();
};

const openEditModal = (mod) => {
  isEdit.value = true;
  currentId.value = mod.id;
  form.code = mod.code;
  form.name = mod.name;
  form.is_active = mod.is_active;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/modes-of-delivery/${currentId.value}`, form);
    } else {
      response = await axios.post('/modes-of-delivery', form);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      bootstrapModal.hide();
      fetchMods();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = async (mod) => {
  const result = await Swal.fire({
    title: 'Hapus Mode of Delivery?',
    text: `Apakah Anda yakin ingin menghapus MOD "${mod.name}"? Tindakan ini tidak dapat dibatalkan.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });

  if (!result.isConfirmed) return;

  try {
    const response = await axios.delete(`/modes-of-delivery/${mod.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchMods();
    }
  } catch (err) {
    toast.error('Gagal menghapus mode of delivery.');
  }
};
</script>
