<template>
  <footer class="app-footer px-4 py-3 mt-auto text-center border-top-card bg-dark-card">
    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between text-muted small">
      <span>&copy; {{ currentYear }} Transportation Management System (TMS). All rights reserved.</span>
      <span class="mt-2 mt-sm-0">
        Membangun Masa Depan Logistik yang Terkoneksi.
      </span>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue';

const currentYear = ref(new Date().getFullYear());
</script>

<style scoped>
.app-footer {
  background-color: #111827;
  border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.text-muted {
  color: #8c98a5 !important;
}
</style>
