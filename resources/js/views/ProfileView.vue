<template>
  <div>
    <!-- Profile Card -->
    <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-col items-center w-full gap-6 xl:flex-row">
          <div class="w-20 h-20 overflow-hidden border border-gray-200 rounded-full dark:border-gray-800 bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
            <span class="text-3xl font-bold text-blue-600 dark:text-blue-200">
              {{ userInitial }}
            </span>
          </div>
          <div class="order-3 xl:order-2">
            <h4 class="mb-2 text-lg font-semibold text-center text-gray-800 dark:text-white/90 xl:text-left">
              {{ authStore.user?.name || 'User' }}
            </h4>
            <div class="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left">
              <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ authStore.user?.role_name || 'User' }}</p>
              <div class="hidden h-3.5 w-px bg-gray-300 dark:bg-gray-700 xl:block"></div>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ authStore.user?.email || 'N/A' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Personal Information -->
    <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
      <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
            Personal Information
          </h4>
          <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
            <div>
              <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Full Name</p>
              <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ authStore.user?.name || '-' }}</p>
            </div>
            <div>
              <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Email Address</p>
              <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ authStore.user?.email || '-' }}</p>
            </div>
            <div>
              <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Role</p>
              <p class="text-sm font-medium text-gray-800 dark:text-white/90 capitalize">{{ authStore.user?.role_name || '-' }}</p>
            </div>
            <div>
              <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Status</p>
              <p class="text-sm font-medium text-green-600">Active</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Permissions -->
    <div class="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
      <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-6">
        Permissions
      </h4>
      <div class="flex flex-wrap gap-2">
        <span
          v-for="perm in authStore.user?.permissions || []"
          :key="perm"
          class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
        >
          {{ perm }}
        </span>
        <span v-if="!authStore.user?.permissions?.length" class="text-sm text-gray-500 dark:text-gray-400">
          No permissions assigned.
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();

const userInitial = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.charAt(0).toUpperCase();
});
</script>
