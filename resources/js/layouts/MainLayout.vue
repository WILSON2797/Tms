<template>
  <ThemeProvider>
    <div class="min-h-screen xl:flex">
      <!-- Modular Sidebar -->
      <Sidebar />

      <!-- Mobile Sidebar Backdrop overlay -->
      <Backdrop />

      <!-- Main Section -->
      <div
        class="flex-1 transition-all duration-300 ease-in-out flex flex-col min-h-screen"
        :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']"
      >
        <!-- Modular Topbar / Header -->
        <Topbar />

        <!-- View Container -->
        <div class="p-4 md:p-6 flex-grow w-full">
          <router-view />
        </div>

        <!-- Modular Footer -->
        <Footer />
      </div>
    </div>
  </ThemeProvider>
</template>

<script setup>
import Sidebar from './Sidebar.vue';
import Topbar from './Topbar.vue';
import Footer from './Footer.vue';
import Backdrop from './Backdrop.vue';
import ThemeProvider from './ThemeProvider.vue';
import { useSidebarProvider } from '../composables/useSidebar';

const { isExpanded, isHovered } = useSidebarProvider();
</script>
