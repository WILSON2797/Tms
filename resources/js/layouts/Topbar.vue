<template>
  <header class="navbar-header px-4 py-3 d-flex align-items-center justify-content-between">
    <button class="btn-toggle-sidebar" @click="$emit('toggle-sidebar')">
      <i class="bi" :class="sidebarCollapsed ? 'bi-justify-left' : 'bi-justify'"></i>
    </button>

    <div class="d-flex align-items-center gap-3">
      <div class="user-profile text-end">
        <div class="user-name text-white fw-bold">{{ authStore.user?.name }}</div>
        <div class="user-role badge bg-primary text-uppercase">{{ authStore.user?.role_name }}</div>
      </div>
      <button class="btn btn-outline-danger btn-sm border-0" @click="handleLogout">
        <i class="bi bi-box-arrow-right fs-5"></i>
      </button>
    </div>
  </header>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

defineProps({
  sidebarCollapsed: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle-sidebar']);

const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();

const handleLogout = async () => {
  const confirmLogout = confirm('Apakah Anda yakin ingin keluar dari sistem?');
  if (confirmLogout) {
    await authStore.logout();
    toast.info('Anda telah keluar dari sistem.');
    router.push({ name: 'Login' });
  }
};
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');

.navbar-header {
  height: 70px;
  background-color: #111827;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  position: sticky;
  top: 0;
  z-index: 999;
}

.btn-toggle-sidebar {
  background: none;
  border: none;
  color: #9ca3af;
  font-size: 1.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 5px;
  border-radius: 5px;
  transition: background-color 0.2s;
}

.btn-toggle-sidebar:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: #ffffff;
}

.user-name {
  font-size: 0.9rem;
}

.user-role {
  font-size: 0.7rem;
}
</style>
