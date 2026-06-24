<template>
  <div class="user-view">
    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="users" 
      :loading="loading" 
      empty-text="Belum ada data user."
      title="User Management"
      subtitle="Kelola data pengguna sistem, peranan (role), dan kredensial akses."
    >
      <template #actions>
        <button 
          v-if="authStore.hasPermission('create-master')" 
          class="btn btn-primary d-flex align-items-center gap-2"
          @click="openCreateModal"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Tambah User</span>
        </button>
      </template>

      <template #cell(role)="{ value }">
        {{ value ? value.name : '-' }}
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
            v-if="authStore.hasPermission('delete-master') && row.id !== authStore.user?.id" 
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
      id="userModal" 
      tabindex="-1" 
      aria-labelledby="userModalLabel" 
      aria-hidden="true"
      ref="modalRef"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark-card border-card text-gray-900 dark:text-white">
          <div class="modal-header border-secondary-custom">
            <h5 class="modal-title fw-bold" id="userModalLabel">
              {{ isEdit ? 'Edit User' : 'Tambah User' }}
            </h5>
            <button type="button" class="btn-close dark:btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">NAMA LENGKAP</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.name" 
                    placeholder="Nama Lengkap" 
                    required 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">USERNAME</label>
                  <input 
                    type="text" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.username" 
                    placeholder="Username" 
                    required 
                  />
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">
                    PASSWORD {{ isEdit ? '(OPSIONAL)' : '' }}
                  </label>
                  <input 
                    type="password" 
                    class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
                    v-model="form.password" 
                    placeholder="Password minimal 6 karakter" 
                    :required="!isEdit"
                  />
                  <small v-if="isEdit" class="text-muted block mt-1">
                    Kosongkan jika tidak ingin mengubah password.
                  </small>
                </div>
                <div class="col-12">
                  <label class="form-label text-muted small mb-1">ROLE / PERAN</label>
                  <v-select
                    :options="roles"
                    label="name"
                    :reduce="role => role.id"
                    v-model="form.role_id"
                    placeholder="Pilih Role"
                    :clearable="false"
                    append-to-body
                  />
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
const users = ref([]);
const roles = ref([]);
const modalRef = ref(null);
let bootstrapModal = null;

const isEdit = ref(false);
const editingId = ref(null);

const form = reactive({
  name: '',
  username: '',
  password: '',
  role_id: null
});

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'name', header: 'Nama Lengkap' },
  { accessorKey: 'username', header: 'Username' },
  { accessorKey: 'role', header: 'Role' },
  { accessorKey: 'created_at', header: 'Tanggal Dibuat' },
  { accessorKey: 'actions', header: 'Aksi' }
];

onMounted(() => {
  bootstrapModal = new Modal(modalRef.value);
  fetchUsers();
  fetchRoles();
});

const fetchUsers = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/users');
    if (response.data.success) {
      users.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data user.');
  } finally {
    loading.value = false;
  }
};

const fetchRoles = async () => {
  try {
    const response = await axios.get('/roles');
    if (response.data.success) {
      roles.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil daftar role.');
  }
};

const openCreateModal = () => {
  isEdit.value = false;
  editingId.value = null;
  form.name = '';
  form.username = '';
  form.password = '';
  form.role_id = roles.value.length > 0 ? roles.value[0].id : null;
  bootstrapModal.show();
};

const openEditModal = (user) => {
  isEdit.value = true;
  editingId.value = user.id;
  form.name = user.name;
  form.username = user.username;
  form.password = '';
  form.role_id = user.role_id;
  bootstrapModal.show();
};

const handleSubmit = async () => {
  if (!form.role_id) {
    toast.error('Silakan pilih role terlebih dahulu.');
    return;
  }
  
  submitLoading.value = true;
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/users/${editingId.value}`, form);
    } else {
      response = await axios.post('/users', form);
    }

    if (response.data.success) {
      toast.success(response.data.message);
      bootstrapModal.hide();
      fetchUsers();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitLoading.value = false;
  }
};

const handleDelete = async (user) => {
  const result = await Swal.fire({
    title: 'Hapus User?',
    text: `Apakah Anda yakin ingin menghapus user "${user.name}"? Tindakan ini tidak dapat dibatalkan.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });

  if (!result.isConfirmed) return;

  try {
    const response = await axios.delete(`/users/${user.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchUsers();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus user.');
  }
};
</script>
