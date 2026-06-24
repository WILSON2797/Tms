<template>
  <div class="shipment-order-view">
    <!-- Table Card -->
    <DataTable 
      :columns="columns" 
      :data="orders" 
      :loading="loading" 
      empty-text="Belum ada data shipment order."
      title="Shipment Orders (Jobs)"
      subtitle="Kelola order pengiriman, nomor referensi customer, kota asal/tujuan, penerima, dan tipe order."
    >
      <template #actions>
        <router-link 
          v-if="authStore.hasPermission('create-shipment')" 
          :to="{ name: 'CreateShipmentOrder' }"
          class="btn btn-primary d-flex align-items-center gap-2"
        >
          <i class="bi bi-plus-lg"></i>
          <span>Buat Shipment Order</span>
        </router-link>
      </template>

      <template #cell(job_number)="{ value }">
        <span class="text-info fw-bold">{{ value }}</span>
      </template>
      <template #cell(order_type)="{ value }">
        <span :class="value === 'URGENT' ? 'text-danger fw-semibold' : 'text-info fw-semibold'">
          {{ value }}
        </span>
      </template>
      <template #cell(status)="{ value }">
        <span class="badge" :class="getStatusBadgeClass(value)">
          {{ value }}
        </span>
      </template>
      <template #cell(actions)="{ row }">
        <div class="d-flex gap-2">
          <!-- Edit: allowed for editing -->
          <router-link 
            v-if="authStore.hasPermission('edit-shipment')" 
            :to="{ name: 'EditShipmentOrder', params: { id: row.id } }"
            class="btn btn-sm btn-outline-info border-0"
            title="Edit Order"
          >
            <i class="bi bi-pencil-square"></i>
          </router-link>
          <!-- Delete: only allowed for DRAFT orders -->
          <button 
            v-if="authStore.hasPermission('delete-shipment') && row.status === 'DRAFT'" 
            class="btn btn-sm btn-outline-danger border-0" 
            @click="handleDelete(row)"
            title="Hapus Order"
          >
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </template>
    </DataTable>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import DataTable from '../../components/DataTable.vue';

const authStore = useAuthStore();
const toast = useToast();
const loading = ref(false);
const orders = ref([]);

const columns = [
  { accessorKey: 'no', header: 'No', meta: { disableSearch: true, width: '55px', align: 'center' } },
  { accessorKey: 'job_number', header: 'Job Number' },
  { accessorKey: 'customer.customer_name', header: 'Customer' },
  { accessorKey: 'order_date', header: 'Tgl Order' },
  { accessorKey: 'origin_city', header: 'Asal' },
  { accessorKey: 'destination_city', header: 'Tujuan' },
  { accessorKey: 'receiver_name', header: 'Penerima' },
  { accessorKey: 'order_type', header: 'Tipe' },
  { accessorKey: 'status', header: 'Status' },
  { accessorKey: 'actions', header: 'Aksi' }
];

onMounted(() => {
  fetchOrders();
});

const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/shipment-orders');
    if (response.data.success) {
      orders.value = response.data.data;
    }
  } catch (err) {
    toast.error('Gagal mengambil data shipment order.');
  } finally {
    loading.value = false;
  }
};

const handleDelete = async (order) => {
  const result = await Swal.fire({
    title: 'Hapus Shipment Order?',
    text: `Apakah Anda yakin ingin menghapus order "${order.job_number}"? Tindakan ini tidak dapat dibatalkan.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });

  if (!result.isConfirmed) return;

  try {
    const response = await axios.delete(`/shipment-orders/${order.id}`);
    if (response.data.success) {
      toast.success(response.data.message);
      fetchOrders();
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menghapus shipment order.');
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'DRAFT': return 'bg-secondary';
    case 'ASSIGNED': return 'bg-primary';
    case 'IN_TRANSIT': return 'bg-warning text-dark';
    case 'DELIVERED': return 'bg-success';
    case 'CANCELLED': return 'bg-danger';
    default: return 'bg-light text-dark';
  }
};
</script>

<style scoped>
</style>
