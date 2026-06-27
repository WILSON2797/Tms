<template>
  <aside :class="[
    'fixed flex flex-col top-0 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-[9999] border-r border-gray-200',
    {
      'lg:w-[290px] px-5': isExpanded || isMobileOpen || isHovered,
      'lg:w-[90px] px-4': !isExpanded && !isHovered,
      'translate-x-0 w-[290px]': isMobileOpen,
      '-translate-x-full': !isMobileOpen,
      'lg:translate-x-0': true,
    },
  ]" @mouseenter="!isExpanded && (isHovered = true)" @mouseleave="isHovered = false">
    <!-- Logo area -->
    <div :class="[
      'pt-8 pb-8 flex items-center justify-center relative',
    ]">
      <router-link to="/" class="flex items-center gap-2 no-underline mx-auto">
        <span v-if="isExpanded || isHovered || isMobileOpen"
          class="text-xl font-black tracking-wider text-gray-900 dark:text-white">
          WG <span class="text-blue-600">TMS</span>
        </span>
        <span v-else class="text-lg font-black tracking-wider text-blue-600">
          WG
        </span>
      </router-link>

      <!-- Close Button inside Sidebar on Mobile -->
      <button v-if="isMobileOpen" @click="isMobileOpen = false"
        class="lg:hidden p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 absolute right-0">
        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none"
          xmlns="http://www.w3.org/2000/svg">
          <path fill-rule="evenodd" clip-rule="evenodd"
            d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
            fill="currentColor" />
        </svg>
      </button>
    </div>

    <!-- Divider Line (exactly in the red box position) -->
    <div class="border-b border-gray-200 dark:border-gray-800 mb-6"></div>

    <!-- Navigation items -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar flex-1 pb-10">
      <nav class="mb-6">
        <div class="flex flex-col gap-4">
          <div v-for="(menuGroup, groupIndex) in menuGroups" :key="groupIndex">
            <div :class="[
              'mb-4 text-xs uppercase flex leading-[20px] text-gray-400 font-semibold',
              !isExpanded && !isHovered
                ? 'lg:justify-center'
                : 'justify-start',
            ]">
              <template v-if="isExpanded || isHovered || isMobileOpen">
                {{ menuGroup.title }}
              </template>
              <HorizontalDots v-else />
            </div>
            <ul class="flex flex-col gap-4 list-none p-0 m-0">
              <li v-for="(item, index) in menuGroup.items" :key="item.name">
                <!-- Items with sub-items -->
                <button v-if="item.subItems" @click="toggleSubmenu(groupIndex, index)" :class="[
                  'menu-item group w-full',
                  {
                    'menu-item-active': isSubmenuOpen(groupIndex, index),
                    'menu-item-inactive': !isSubmenuOpen(groupIndex, index),
                  },
                  !isExpanded && !isHovered
                    ? 'lg:justify-center'
                    : 'lg:justify-start',
                ]">
                  <span v-if="item.icon" :class="[
                    isSubmenuOpen(groupIndex, index)
                      ? 'menu-item-icon-active'
                      : 'menu-item-icon-inactive',
                  ]">
                    <component :is="item.icon" />
                  </span>
                  <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text">{{ item.name }}</span>
                  <ChevronDownIcon v-if="isExpanded || isHovered || isMobileOpen" :class="[
                    'ml-auto w-5 h-5 transition-transform duration-200',
                    {
                      'rotate-180 text-brand-500': isSubmenuOpen(
                        groupIndex,
                        index
                      ),
                    },
                  ]" />
                </button>
                <!-- Simple items (no sub-items) -->
                <router-link v-else-if="item.path" :to="item.path" :class="[
                  'menu-item group no-underline',
                  {
                    'menu-item-active': isActive(item.path),
                    'menu-item-inactive': !isActive(item.path),
                  },
                  !isExpanded && !isHovered
                    ? 'lg:justify-center'
                    : 'lg:justify-start',
                ]">
                  <span :class="[
                    isActive(item.path)
                      ? 'menu-item-icon-active'
                      : 'menu-item-icon-inactive',
                  ]">
                    <component :is="item.icon" />
                  </span>
                  <span v-if="isExpanded || isHovered || isMobileOpen" class="menu-item-text">{{ item.name }}</span>
                </router-link>
                <!-- Submenu transition -->
                <transition @enter="startTransition" @after-enter="endTransition" @before-leave="startTransition"
                  @after-leave="endTransition">
                  <div v-show="item.subItems && isSubmenuOpen(groupIndex, index) &&
                    (isExpanded || isHovered || isMobileOpen)
                    ">
                    <ul class="mt-2 space-y-1 ml-9 list-none p-0">
                      <li v-for="subItem in item.subItems" :key="subItem.name">
                        <router-link :to="subItem.path" :class="[
                          'menu-dropdown-item no-underline flex items-center gap-2',
                          {
                            'menu-dropdown-item-active': isActive(subItem.path),
                            'menu-dropdown-item-inactive': !isActive(subItem.path),
                          },
                        ]">
                          <span v-if="subItem.icon" class="w-4 h-4 flex items-center justify-center">
                            <component :is="subItem.icon" class="w-4 h-4" />
                          </span>
                          <span>{{ subItem.name }}</span>
                        </router-link>
                      </li>
                    </ul>
                  </div>
                </transition>
              </li>
            </ul>
          </div>
        </div>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useSidebar } from '../composables/useSidebar';

import {
  GridIcon,
  UserGroupIcon,
  UserCircleIcon,
  BoxCubeIcon,
  FolderIcon,
  ListIcon,
  CalenderIcon,
  PageIcon,
  CheckIcon,
  BarChartIcon,
  HorizontalDots,
  ChevronDownIcon,
  SettingsIcon,
  MapPinIcon,
} from '../icons';

const route = useRoute();
const authStore = useAuthStore();
const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();

const isActive = (path) => {
  if (path === '/') return route.path === '/';
  return route.path.startsWith(path);
};

onMounted(() => {
  if (openSubmenu.value === null) {
    menuGroups.value.forEach((group, gIdx) => {
      group.items.forEach((item, iIdx) => {
        if (item.subItems?.some((subItem) => isActive(subItem.path))) {
          openSubmenu.value = `${gIdx}-${iIdx}`;
        }
      });
    });
  }
});

const menuGroups = computed(() => {
  const groups = [];

  // Menu
  groups.push({
    title: 'Menu',
    items: [
      { name: 'Dashboard', icon: GridIcon, path: '/' }
    ]
  });

  // Operasional
  const opItems = [];
  if (authStore.hasPermission('view-shipment')) {
    opItems.push({ name: 'Shipment Orders', icon: ListIcon, path: '/shipments' });
    opItems.push({ name: 'Waiting Dispatch', icon: CalenderIcon, path: '/create-task' });
    opItems.push({ name: 'Assigned Trips', icon: PageIcon, path: '/trips' });
    opItems.push({ name: 'Tracking Timeline', icon: GridIcon, path: '/tracking' });
  }
  if (authStore.hasPermission('view-pod')) {
    opItems.push({ name: 'Proof of Delivery', icon: CheckIcon, path: '/pod' });
  }
  if (opItems.length > 0) {
    groups.push({ title: 'Operasional', items: opItems });
  }

  // Laporan
  if (authStore.hasPermission('view-master')) {
    groups.push({
      title: 'Laporan',
      items: [
        { name: 'Laporan TMS', icon: BarChartIcon, path: '/reports' }
      ]
    });
  }

  // Master Data
  if (authStore.hasPermission('view-master')) {
    groups.push({
      title: 'Pengaturan',
      items: [
        {
          name: 'Master Setting',
          icon: SettingsIcon,
          subItems: [
            { name: 'Customers', icon: UserGroupIcon, path: '/master/customers' },
            { name: 'Users', icon: UserCircleIcon, path: '/master/users' },
            { name: 'Vehicles', icon: BoxCubeIcon, path: '/master/vehicles' },
            { name: 'Transporters', icon: FolderIcon, path: '/master/transporters' },
            { name: 'Mode of Transport', icon: BoxCubeIcon, path: '/master/modes-of-transport' },
            { name: 'Mode of Delivery', icon: BoxCubeIcon, path: '/master/modes-of-delivery' },
            { name: 'Master Location', icon: MapPinIcon, path: '/master/cities' },
            { name: 'Mobile App Settings', icon: SettingsIcon, path: '/settings/mobile' }
          ]
        }
      ]
    });
  }

  return groups;
});

const toggleSubmenu = (groupIndex, itemIndex) => {
  const key = `${groupIndex}-${itemIndex}`;
  openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isAnySubmenuRouteActive = computed(() => {
  return menuGroups.value.some((group) =>
    group.items.some(
      (item) =>
        item.subItems && item.subItems.some((subItem) => isActive(subItem.path))
    )
  );
});

const isSubmenuOpen = (groupIndex, itemIndex) => {
  const key = `${groupIndex}-${itemIndex}`;
  return openSubmenu.value === key;
};

const startTransition = (el) => {
  el.style.height = 'auto';
  const height = el.scrollHeight;
  el.style.height = '0px';
  el.offsetHeight;
  el.style.height = height + 'px';
};

const endTransition = (el) => {
  el.style.height = '';
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}

.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
