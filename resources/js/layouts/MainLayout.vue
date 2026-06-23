<template>
  <div class="app-layout">
    <!-- Modular Sidebar -->
    <Sidebar :collapsed="sidebarCollapsed" />

    <!-- Main Section -->
    <div class="main-content" :class="{ 'expanded': sidebarCollapsed }">
      <!-- Modular Topbar -->
      <Topbar :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="toggleSidebar" />

      <!-- View Container -->
      <main class="content-body p-4 flex-grow-1">
        <router-view />
      </main>

      <!-- Modular Footer -->
      <Footer />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from './Sidebar.vue';
import Topbar from './Topbar.vue';
import Footer from './Footer.vue';

const sidebarCollapsed = ref(false);

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  width: 100vw;
  background-color: #0b0f19;
  margin: 0;
  padding: 0;
}

/* Main Content Area */
.main-content {
  flex: 1;
  margin-left: 260px;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  min-width: 0; /* Allow the content wrapper to shrink and trigger table overflow */
}

.main-content.expanded {
  margin-left: 70px;
}

.content-body {
  flex: 1;
  overflow-y: auto;
  min-width: 0; /* Allow inner contents like tables to shrink and scroll horizontally */
}
</style>
