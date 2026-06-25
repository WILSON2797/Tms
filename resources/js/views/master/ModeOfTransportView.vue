<template>
  <div class="modes-of-transport-view">
    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="mots" 
      :loading="loading" 
      empty-text="Belum ada data mode of transport."
      title="Mode of Transport Management"
      subtitle="Kelola tipe moda transportasi yang tersedia untuk pengiriman barang (misal: Darat, Laut, Udara)."
    >
      <template #actions>
        <button 
          v-if="authStore.hasPermission('create-master')" 
          class="btn btn-primary d-flex align-items-center gap-2"
          @click="openAddModal"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Mode of Transport</span>
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
      id="motModal" 
      tabindex="-1" 
      aria-labelledby="motModalLabel" 
      aria-hidden="true"
      ref="modalRef"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark-card border-card text-gray-900 dark:text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="motModalLabel">
              {{ isEdit ? 'Edit Mode of Transport' : 'Tambah Mode of Transport' }}
            </h5>
            <button type="button" class="btn-close dark:btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">KODE MOT</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.code" 
                    placeholder="E.g. LAND, SEA, AIR" 
                    required 
                    :disabled="isEdit"
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">NAMA MOT</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.name" 
                    placeholder="E.g. Transportasi Darat" 
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
const mots = ref([]);
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
  { accessorKey: 'code', header: 'Kode MOT' },
  { accessorKey: 'name', header: 'Nama MOT' },
  { accessorKey: 'is_active', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

onMounted(() => {
  bootstrapModal = new Modal(modalRef.value);
  fetchMots();
});

const fetchMots = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/modes-of-transport/all');
    if (response.data.success) {
      mots.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data mode of transport.');
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

const openEditModal = (mot) => {
  isEdit.value = true;
  currentId.value = mot.id;
  form.code = mot.code;
  form.name = mot.name;
  form.is_active = mot.is_active;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/modes-of-transport/${currentId.value}`, form);
    } else {
      response = await axios.post('/modes-of-transport', form);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      bootstrapModal.hide();
      fetchMots();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = async (mot) => {
  const result = await Swal.fire({
    title: 'Hapus Mode of Transport?',
    text: `Apakah Anda yakin ingin menghapus MOT "${mot.name}"? Tindakan ini tidak dapat dibatalkan.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });

  if (!result.isConfirmed) return;

  try {
    const response = await axios.delete(`/modes-of-transport/${mot.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchMots();
    }
  } catch (err) {
    toast.error('Gagal menghapus mode of transport.');
  }
};
</script>
