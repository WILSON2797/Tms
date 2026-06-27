<template>
  <div class="modes-of-transport-view">
    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="cities" 
      :loading="loading" 
      empty-text="Belum ada data Kota/Kec."
      title="Kota / Kec Management"
      subtitle="Kelola daftar Kota/Kec beserta provinsi untuk tujuan rute pengiriman."
    >
      <template #actions>
        <button 
          v-if="authStore.hasPermission('create-master')" 
          class="btn btn-primary d-flex align-items-center gap-2"
          @click="openAddModal"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Kota/Kec</span>
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
      id="cityModal" 
      tabindex="-1" 
      aria-labelledby="cityModalLabel" 
      aria-hidden="true"
      ref="modalRef"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark-card border-card text-gray-900 dark:text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="cityModalLabel">
              {{ isEdit ? 'Edit Kota/Kec' : 'Tambah Kota/Kec' }}
            </h5>
            <button type="button" class="btn-close dark:btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">PROVINSI</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.province" 
                    placeholder="E.g. Jawa Barat, Jawa Timur" 
                    required 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">KABUPATEN / KOTA</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.name" 
                    placeholder="E.g. Bekasi, Bandung, Toba Samosir" 
                    required 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">KECAMATAN (DISTRICT)</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.district" 
                    placeholder="E.g. Cikarang Selatan, Cimahi, Ajibata" 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">TIPE WILAYAH</label>
                  <v-select 
                    v-model="form.type" 
                    :options="['Kabupaten', 'Kota']" 
                    placeholder="Pilih Tipe..." 
                    :clearable="false"
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
const cities = ref([]);
const modalRef = ref(null);
let bootstrapModal = null;

const isEdit = ref(false);
const currentId = ref(null);

const form = reactive({
  name: '',
  district: '',
  type: 'Kota',
  province: '',
  is_active: true
});

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'province', header: 'Provinsi' },
  { accessorKey: 'name', header: 'Kota/Kabupaten' },
  { accessorKey: 'district', header: 'Kecamatan' },
  { accessorKey: 'type', header: 'Tipe' },
  { accessorKey: 'is_active', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

onMounted(() => {
  bootstrapModal = new Modal(modalRef.value);
  fetchCities();
});

const fetchCities = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/cities/all');
    if (response.data.success) {
      cities.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data Kota/Kec.');
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEdit.value = false;
  currentId.value = null;
  form.name = '';
  form.district = '';
  form.type = 'Kota';
  form.province = '';
  form.is_active = true;
  bootstrapModal.show();
};

const openEditModal = (city) => {
  isEdit.value = true;
  currentId.value = city.id;
  form.name = city.name;
  form.district = city.district || '';
  form.type = city.type;
  form.province = city.province;
  form.is_active = city.is_active;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/cities/${currentId.value}`, form);
    } else {
      response = await axios.post('/cities', form);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      bootstrapModal.hide();
      fetchCities();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = async (city) => {
  const result = await Swal.fire({
    title: 'Hapus Kabupaten/Kota?',
    text: `Apakah Anda yakin ingin menghapus "${city.type} ${city.name}"? Tindakan ini tidak dapat dibatalkan.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });

  if (!result.isConfirmed) return;

  try {
    const response = await axios.delete(`/cities/${city.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchCities();
    }
  } catch (err) {
    toast.error('Gagal menghapus kabupaten/kota.');
  }
};
</script>
