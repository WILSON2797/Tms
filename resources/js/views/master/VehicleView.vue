<template>
  <div class="vehicle-view">
    <!-- Table Card -->
    <DataTable :columns="columns" :data="vehicles" :loading="loading" empty-text="Belum ada data kendaraan."
      title="Vehicle Management"
      subtitle="Kelola armada kendaraan, tipe, kepemilikan, dan kapasitas muatan (berat/volume).">
      <template #actions>
        <button v-if="authStore.hasPermission('create-master')" class="btn btn-primary d-flex align-items-center gap-2"
          @click="openAddModal">
          <i class="bi bi-plus-lg"></i>
          <span>Tambah Kendaraan</span>
        </button>
      </template>

      <template #cell(capacity_weight)="{ value }">
        {{ value }} Kg
      </template>
      <template #cell(capacity_volume)="{ value }">
        {{ value }} CBM
      </template>
      <template #cell(ownership)="{ value }">
        <span :class="value === 'internal' ? 'text-info fw-semibold' : 'text-warning fw-semibold'">
          {{ value === 'internal' ? 'Internal' : 'Eksternal / Vendor' }}
        </span>
      </template>
      <template #cell(is_active)="{ value }">
        <span class="badge" :class="value ? 'bg-success' : 'bg-danger'">
          {{ value ? 'Aktif' : 'Non-Aktif' }}
        </span>
      </template>
      <template #cell(actions)="{ row }">
        <div class="d-flex gap-2">
          <button v-if="authStore.hasPermission('edit-master')" class="btn btn-sm btn-outline-info border-0"
            @click="openEditModal(row)">
            <i class="bi bi-pencil-square"></i>
          </button>
          <button v-if="authStore.hasPermission('delete-master')" class="btn btn-sm btn-outline-danger border-0"
            @click="handleDelete(row)">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </template>
    </DataTable>

    <!-- Modal Form -->
    <div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true"
      ref="modalRef">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark-card border-card text-gray-900 dark:text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="vehicleModalLabel">
              {{ isEdit ? 'Edit Kendaraan' : 'Tambah Kendaraan' }}
            </h5>
            <button type="button" class="btn-close dark:btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">NO POLISI</label>
                  <input type="text" class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom"
                    v-model="form.vehicle_no" placeholder="E.g. B 1234 CD" required :disabled="isEdit" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">TIPE KENDARAAN</label>
                  <input type="text" class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom"
                    v-model="form.vehicle_type" placeholder="E.g. CDE Box / CDD / Wingbox" required />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">BRAND (MEREK)</label>
                  <input type="text" class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom"
                    v-model="form.brand" placeholder="E.g. Isuzu / Hino" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">MODEL</label>
                  <input type="text" class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom"
                    v-model="form.model" placeholder="E.g. Elf / Dutro" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KAPASITAS BERAT (KG)</label>
                  <input type="number" step="0.01"
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom"
                    v-model="form.capacity_weight" placeholder="E.g. 2000.00" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KAPASITAS VOLUME (CBM)</label>
                  <input type="number" step="0.01"
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom"
                    v-model="form.capacity_volume" placeholder="E.g. 6.00" />
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label text-muted small mb-1">KEPEMILIKAN (OWNERSHIP)</label>
                  <select class="form-select bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" v-model="form.ownership"
                    required>
                    <option value="internal">Internal</option>
                    <option value="external">Eksternal (Vendor / Transporter)</option>
                  </select>
                </div>
                <div class="col-12 col-md-6 d-flex align-items-center mt-auto">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="isActiveSwitch" v-model="form.is_active" />
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
import DataTable from '../../components/DataTable.vue';
import { Modal } from 'bootstrap';

const authStore = useAuthStore();
const toast = useToast();
const loading = ref(false);
const submitLoading = ref(false);
const vehicles = ref([]);
const searchQuery = ref('');
const modalRef = ref(null);
let bootstrapModal = null;

const isEdit = ref(false);
const currentId = ref(null);

const form = reactive({
  vehicle_no: '',
  vehicle_type: '',
  brand: '',
  model: '',
  capacity_weight: 0,
  capacity_volume: 0,
  ownership: 'internal',
  is_active: true
});

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'vehicle_no', header: 'No Polisi' },
  { accessorKey: 'vehicle_type', header: 'Tipe' },
  { accessorKey: 'brand', header: 'Merek' },
  { accessorKey: 'capacity_weight', header: 'Kapasitas Berat' },
  { accessorKey: 'capacity_volume', header: 'Kapasitas Volume' },
  { accessorKey: 'ownership', header: 'Kepemilikan' },
  { accessorKey: 'is_active', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

const filteredVehicles = computed(() => {
  if (!searchQuery.value) return vehicles.value;
  const query = searchQuery.value.toLowerCase();
  return vehicles.value.filter(v =>
    v.vehicle_no.toLowerCase().includes(query) ||
    v.vehicle_type.toLowerCase().includes(query) ||
    (v.brand && v.brand.toLowerCase().includes(query))
  );
});

onMounted(() => {
  bootstrapModal = new Modal(modalRef.value);
  fetchVehicles();
});

const fetchVehicles = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/vehicles');
    if (response.data.success) {
      vehicles.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data kendaraan.');
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEdit.value = false;
  currentId.value = null;
  form.vehicle_no = '';
  form.vehicle_type = '';
  form.brand = '';
  form.model = '';
  form.capacity_weight = 0;
  form.capacity_volume = 0;
  form.ownership = 'internal';
  form.is_active = true;
  bootstrapModal.show();
};

const openEditModal = (vehicle) => {
  isEdit.value = true;
  currentId.value = vehicle.id;
  form.vehicle_no = vehicle.vehicle_no;
  form.vehicle_type = vehicle.vehicle_type;
  form.brand = vehicle.brand;
  form.model = vehicle.model;
  form.capacity_weight = vehicle.capacity_weight;
  form.capacity_volume = vehicle.capacity_volume;
  form.ownership = vehicle.ownership;
  form.is_active = vehicle.is_active;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/vehicles/${currentId.value}`, form);
    } else {
      response = await axios.post('/vehicles', form);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      bootstrapModal.hide();
      fetchVehicles();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = async (vehicle) => {
  const confirmDelete = confirm(`Apakah Anda yakin ingin menghapus kendaraan "${vehicle.vehicle_no}"?`);
  if (!confirmDelete) return;

  try {
    const response = await axios.delete(`/vehicles/${vehicle.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchVehicles();
    }
  } catch (err) {
    toast.error('Gagal menghapus kendaraan.');
  }
};
</script>


