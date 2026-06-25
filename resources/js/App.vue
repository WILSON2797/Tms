<template>
  <router-view />
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useAuthStore } from './stores/auth';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();

const INACTIVITY_TIMEOUT = 25 * 60 * 1000; // 25 minutes in milliseconds
const CHECK_INTERVAL = 10000; // Check every 10 seconds
let checkTimer = null;
let lastUpdate = 0;

const updateActivity = () => {
  const now = Date.now();
  // Throttle updates to localStorage once every 5 seconds to optimize performance
  if (now - lastUpdate > 5000) {
    localStorage.setItem('last_active_time', now.toString());
    lastUpdate = now;
  }
};

const checkInactivity = async () => {
  if (!authStore.isAuthenticated) return;

  const lastActiveStr = localStorage.getItem('last_active_time');
  if (!lastActiveStr) {
    // If not set yet, initialize it
    localStorage.setItem('last_active_time', Date.now().toString());
    return;
  }

  const lastActive = parseInt(lastActiveStr, 10);
  const now = Date.now();

  if (now - lastActive > INACTIVITY_TIMEOUT) {
    // Session expired due to inactivity
    toast.warning('Sesi Anda telah berakhir karena tidak ada aktivitas selama 25 menit.');
    await authStore.logout();
    router.push({ name: 'Login' });
  }
};

onMounted(() => {
  // Activity event listeners
  window.addEventListener('mousemove', updateActivity);
  window.addEventListener('keydown', updateActivity);
  window.addEventListener('click', updateActivity);
  window.addEventListener('scroll', updateActivity);
  window.addEventListener('touchstart', updateActivity);

  // Initialize last_active_time if logged in and not present
  if (authStore.isAuthenticated) {
    const lastActiveStr = localStorage.getItem('last_active_time');
    if (!lastActiveStr) {
      localStorage.setItem('last_active_time', Date.now().toString());
    } else {
      // Check immediately on load (handles browser close case)
      checkInactivity();
    }
  }

  // Periodic inactivity checker
  checkTimer = setInterval(checkInactivity, CHECK_INTERVAL);
});

onUnmounted(() => {
  // Clean up listeners
  window.removeEventListener('mousemove', updateActivity);
  window.removeEventListener('keydown', updateActivity);
  window.removeEventListener('click', updateActivity);
  window.removeEventListener('scroll', updateActivity);
  window.removeEventListener('touchstart', updateActivity);

  if (checkTimer) {
    clearInterval(checkTimer);
  }
});
</script>
