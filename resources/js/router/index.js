import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginView.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('../layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../views/DashboardView.vue')
      },
      {
        path: 'master/customers',
        name: 'Customers',
        component: () => import('../views/master/CustomerView.vue'),
        meta: { permission: 'view-master' }
      },
      {
        path: 'master/drivers',
        name: 'Drivers',
        component: () => import('../views/master/DriverView.vue'),
        meta: { permission: 'view-master' }
      },
      {
        path: 'master/vehicles',
        name: 'Vehicles',
        component: () => import('../views/master/VehicleView.vue'),
        meta: { permission: 'view-master' }
      },
      {
        path: 'master/transporters',
        name: 'Transporters',
        component: () => import('../views/master/TransporterView.vue'),
        meta: { permission: 'view-master' }
      },
      {
        path: 'shipments',
        name: 'ShipmentOrders',
        component: () => import('../views/shipment/ShipmentOrderView.vue'),
        meta: { permission: 'view-shipment' }
      },
      {
        path: 'shipments/create',
        name: 'CreateShipmentOrder',
        component: () => import('../views/shipment/ShipmentOrderForm.vue'),
        meta: { permission: 'create-shipment' }
      },
      {
        path: 'shipments/:id/edit',
        name: 'EditShipmentOrder',
        component: () => import('../views/shipment/ShipmentOrderForm.vue'),
        meta: { permission: 'edit-shipment' }
      },
      {
        path: 'create-task',
        name: 'CreateTask',
        component: () => import('../views/shipment/TripPlanningView.vue'),
        meta: { permission: 'view-shipment' }
      },
      {
        path: 'trips',
        name: 'Trips',
        component: () => import('../views/shipment/TripView.vue'),
        meta: { permission: 'view-shipment' }
      },
      {
        path: 'tracking',
        name: 'Tracking',
        component: () => import('../views/TrackingView.vue'),
        meta: { permission: 'view-shipment' }
      },
      {
        path: 'pod',
        name: 'POD',
        component: () => import('../views/PODView.vue'),
        meta: { permission: 'view-pod' }
      },
      {
        path: 'reports',
        name: 'Reports',
        component: () => import('../views/ReportView.vue'),
        meta: { permission: 'view-master' }
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.fetchUser();
  }

  const isRequiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const isGuest = to.matched.some(record => record.meta.guest);

  if (isRequiresAuth) {
    if (!authStore.isAuthenticated) {
      next({ name: 'Login' });
    } else {
      // Find required permission from matched route components
      const requiredPermission = to.matched.find(r => r.meta.permission)?.meta.permission;
      if (requiredPermission && !authStore.hasPermission(requiredPermission)) {
        next({ name: 'Dashboard' });
      } else {
        next();
      }
    }
  } else if (isGuest) {
    if (authStore.isAuthenticated) {
      next({ name: 'Dashboard' });
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;
