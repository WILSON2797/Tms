<template>
  <footer class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-4 px-6 mt-auto">
    <div class="flex flex-col sm:flex-row items-center justify-between text-gray-500 dark:text-gray-400 text-xs gap-2">
      <span>&copy; {{ currentYear }} Transportation Management System (TMS). All rights reserved.</span>
      <span class="sm:text-right">Membangun Masa Depan Logistik yang Terkoneksi.</span>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue';
const currentYear = ref(new Date().getFullYear());
</script>
