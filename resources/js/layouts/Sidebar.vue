<template>
  <aside class="sidebar" :class="{ 'collapsed': collapsed }">
    <div class="sidebar-brand">
      <div class="logo-circle-sm">
        <i class="bi bi-truck text-primary"></i>
      </div>
      <span class="brand-text text-white fw-bold ms-2">TMS Admin</span>
    </div>

    <nav class="sidebar-nav">
      <!-- Dashboard Link -->
      <router-link :to="{ name: 'Dashboard' }" class="nav-item">
        <i class="bi bi-grid-1x2"></i>
        <span>Dashboard</span>
      </router-link>

      <div class="nav-section-title" v-if="authStore.hasPermission('view-master')">Master Data</div>
      
      <router-link v-if="authStore.hasPermission('view-master')" :to="{ name: 'Customers' }" class="nav-item">
        <i class="bi bi-people"></i>
        <span>Customers</span>
      </router-link>
      
      <router-link v-if="authStore.hasPermission('view-master')" :to="{ name: 'Drivers' }" class="nav-item">
        <i class="bi bi-person-badge"></i>
        <span>Drivers</span>
      </router-link>
      
      <router-link v-if="authStore.hasPermission('view-master')" :to="{ name: 'Vehicles' }" class="nav-item">
        <i class="bi bi-car-front"></i>
        <span>Vehicles</span>
      </router-link>
      
      <router-link v-if="authStore.hasPermission('view-master')" :to="{ name: 'Transporters' }" class="nav-item">
        <i class="bi bi-building"></i>
        <span>Transporters</span>
      </router-link>

      <div class="nav-section-title">Operasional</div>

      <router-link v-if="authStore.hasPermission('view-shipment')" :to="{ name: 'ShipmentOrders' }" class="nav-item">
        <i class="bi bi-box-seam"></i>
        <span>Shipment Orders</span>
      </router-link>

      <router-link v-if="authStore.hasPermission('view-shipment')" :to="{ name: 'CreateTask' }" class="nav-item">
        <i class="bi bi-calendar-plus"></i>
        <span>Waiting Dispatch</span>
      </router-link>

      <router-link v-if="authStore.hasPermission('view-shipment')" :to="{ name: 'Trips' }" class="nav-item">
        <i class="bi bi-map"></i>
        <span>Assigned Trips</span>
      </router-link>

      <router-link v-if="authStore.hasPermission('view-shipment')" :to="{ name: 'Tracking' }" class="nav-item">
        <i class="bi bi-geo-alt"></i>
        <span>Tracking Timeline</span>
      </router-link>

      <router-link v-if="authStore.hasPermission('view-pod')" :to="{ name: 'POD' }" class="nav-item">
        <i class="bi bi-file-earmark-check"></i>
        <span>Proof of Delivery</span>
      </router-link>

      <div class="nav-section-title" v-if="authStore.hasPermission('view-master')">Laporan</div>

      <router-link v-if="authStore.hasPermission('view-master')" :to="{ name: 'Reports' }" class="nav-item">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Laporan TMS</span>
      </router-link>
    </nav>
  </aside>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';

defineProps({
  collapsed: {
    type: Boolean,
    default: false
  }
});

const authStore = useAuthStore();
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');

.sidebar {
  width: 260px;
  background-color: #111827;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
  display: flex;
  flex-direction: column;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: fixed;
  top: 0;
  bottom: 0;
  left: 0;
  z-index: 1000;
}

.sidebar.collapsed {
  width: 70px;
}

.sidebar-brand {
  height: 70px;
  display: flex;
  align-items: center;
  padding: 0 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar.collapsed .brand-text {
  display: none;
}

.logo-circle-sm {
  width: 32px;
  height: 32px;
  background: rgba(13, 110, 253, 0.15);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(13, 110, 253, 0.2);
}

.sidebar-nav {
  flex: 1;
  padding: 20px 10px;
  overflow-y: auto;
}

.nav-section-title {
  color: #4b5563;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 15px 15px 5px 15px;
  letter-spacing: 0.05em;
}

.sidebar.collapsed .nav-section-title {
  display: none;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 10px 15px;
  color: #9ca3af;
  text-decoration: none;
  border-radius: 8px;
  margin-bottom: 5px;
  transition: all 0.2s ease;
  font-size: 0.95rem;
}

.nav-item i {
  font-size: 1.2rem;
  margin-right: 12px;
}

.sidebar.collapsed .nav-item span {
  display: none;
}

.sidebar.collapsed .nav-item i {
  margin-right: 0;
  width: 100%;
  text-align: center;
}

.nav-item:hover {
  background-color: rgba(255, 255, 255, 0.03);
  color: #f3f4f6;
}

.nav-item.router-link-active {
  background-color: #0d6efd;
  color: #ffffff;
  box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
}
</style>
