<template>
  <div class="dashboard-view">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <div>
        <h3 class="fw-bold text-gray-900 mb-1">Dashboard</h3>
        <p class="text-muted">Selamat datang kembali, {{ authStore.user?.name }}. Berikut adalah ringkasan hari ini.</p>
      </div>
      <div class="text-gray-900 bg-white border-card px-3 py-2 rounded-3 d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-calendar-event text-primary"></i>
        <span>{{ currentDate }}</span>
      </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="row g-4 mb-4">
      <div class="col-12 col-sm-6 col-xl-3" v-for="stat in stats" :key="stat.title">
        <div class="stat-card p-4 d-flex align-items-center justify-content-between bg-white shadow-sm">
          <div>
            <span class="text-muted text-uppercase fs-7 small fw-bold d-block mb-1">{{ stat.title }}</span>
            <h2 class="text-gray-900 fw-bold mb-0">{{ stat.value }}</h2>
          </div>
          <div class="icon-box" :class="stat.colorClass">
            <i class="bi" :class="stat.icon"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions & Info Panel -->
    <div class="row g-4">
      <!-- Quick Action Panel -->
      <div class="col-12 col-lg-8">
        <div class="card bg-white border-card p-4 h-100 shadow-sm">
          <h5 class="text-gray-900 fw-bold mb-3">Quick Navigation & Actions</h5>
          <div class="row g-3">
            <div class="col-12 col-md-6" v-if="authStore.hasPermission('create-shipment')">
              <div class="action-btn-card p-3 d-flex align-items-center gap-3" @click="goToRoute('Shipments')">
                <div class="action-icon bg-primary-soft">
                  <i class="bi bi-box-seam text-primary"></i>
                </div>
                <div>
                  <h6 class="text-gray-900 fw-bold mb-0">Create Shipment</h6>
                  <small class="text-muted">Input order pengiriman baru</small>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6" v-if="authStore.hasPermission('create-trip')">
              <div class="action-btn-card p-3 d-flex align-items-center gap-3" @click="goToRoute('Trips')">
                <div class="action-icon bg-success-soft">
                  <i class="bi bi-map text-success"></i>
                </div>
                <div>
                  <h6 class="text-gray-900 fw-bold mb-0">Trip Planning</h6>
                  <small class="text-muted">Buat trip & combine shipment</small>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6" v-if="authStore.hasPermission('view-shipment')">
              <div class="action-btn-card p-3 d-flex align-items-center gap-3" @click="goToRoute('Tracking')">
                <div class="action-icon bg-warning-soft">
                  <i class="bi bi-geo-alt text-warning"></i>
                </div>
                <div>
                  <h6 class="text-gray-900 fw-bold mb-0">Track Shipments</h6>
                  <small class="text-muted">Pantau timeline pengiriman</small>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6" v-if="authStore.hasPermission('view-pod')">
              <div class="action-btn-card p-3 d-flex align-items-center gap-3" @click="goToRoute('POD')">
                <div class="action-icon bg-info-soft">
                  <i class="bi bi-file-earmark-check text-info"></i>
                </div>
                <div>
                  <h6 class="text-gray-900 fw-bold mb-0">Proof of Delivery</h6>
                  <small class="text-muted">Kelola data penerimaan (POD)</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Info Panel -->
      <div class="col-12 col-lg-4">
        <div class="card bg-white border-card p-4 h-100 shadow-sm">
          <h5 class="text-gray-900 fw-bold mb-3">Informasi Akun</h5>
          <div class="mb-3">
            <span class="text-muted d-block small">NAMA PENGGUNA</span>
            <span class="text-gray-900 fw-bold">{{ authStore.user?.name }}</span>
          </div>
          <div class="mb-3">
            <span class="text-muted d-block small">EMAIL</span>
            <span class="text-gray-900 fw-bold">{{ authStore.user?.email }}</span>
          </div>
          <div class="mb-3">
            <span class="text-muted d-block small">ROLE LEVEL</span>
            <span class="user-role badge bg-primary text-uppercase">{{ authStore.user?.role_name }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const currentDate = ref('');
const stats = ref([
  { title: 'Total Orders Today', value: '12', icon: 'bi-box-seam', colorClass: 'bg-primary-soft text-primary' },
  { title: 'Pending Orders', value: '4', icon: 'bi-clock-history', colorClass: 'bg-warning-soft text-warning' },
  { title: 'In Transit', value: '5', icon: 'bi-truck', colorClass: 'bg-info-soft text-info' },
  { title: 'Delivered', value: '3', icon: 'bi-check-circle', colorClass: 'bg-success-soft text-success' }
]);

onMounted(() => {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  currentDate.value = new Date().toLocaleDateString('id-ID', options);
});

const goToRoute = (routeName) => {
  router.push({ name: routeName });
};
</script>

<style scoped>
.dashboard-view {
  min-height: 100%;
}

.text-muted {
  color: #667085 !important;
}

.border-card {
  border: 1px solid rgba(0, 0, 0, 0.08) !important;
  border-radius: 12px;
}

/* Stats Card */
.stat-card {
  background-color: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

.icon-box {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

/* Soft Color Backgrounds */
.bg-primary-soft {
  background-color: rgba(13, 110, 253, 0.1) !important;
}
.bg-warning-soft {
  background-color: rgba(255, 193, 7, 0.1) !important;
}
.bg-info-soft {
  background-color: rgba(13, 202, 240, 0.1) !important;
}
.bg-success-soft {
  background-color: rgba(25, 135, 84, 0.1) !important;
}

/* Action Button Card */
.action-btn-card {
  background-color: #f9fafb;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn-card:hover {
  background-color: #f3f4f6;
  border-color: rgba(13, 110, 253, 0.3);
  transform: translateY(-2px);
}

.action-icon {
  width: 45px;
  height: 45px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}
</style>
