<template>
  <div class="login-page d-flex min-vh-100 w-100">
    <!-- Left Section: Visual Branding (Visible on md and up) -->
    <div class="login-visual-section d-none d-lg-flex flex-column justify-content-between p-5 col-lg-7 text-white position-relative overflow-hidden">
      <!-- Background subtle grid & blobs -->
      <div class="visual-bg-pattern"></div>
      
      <!-- Logo Header -->
      <div class="d-flex align-items-center gap-3 z-3">
        <div class="logo-box d-flex align-items-center justify-content-center overflow-hidden">
          <img src="/Logo.png" alt="Logo" class="w-100 h-100 object-fit-cover" />
        </div>
        <div>
          <h4 class="fw-bold mb-0 text-dark-blue tracking-wide">TMS PORTAL</h4>
          <span class="text-xs text-muted fw-semibold">TRANSPORT SYSTEM v2.0</span>
        </div>
      </div>

      <!-- Main Visual Feature -->
      <div class="my-auto z-3 max-w-md">
        <div class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill fw-bold mb-3">FLEET & SHIPMENT TRACKING</div>
        <h1 class="display-5 fw-extrabold text-slate-900 mb-3 lh-sm">
          Optimize your logistics <br />
          <span class="text-gradient">in real-time.</span>
        </h1>
        <p class="text-slate-600 fs-5 lh-base">
          Manage drivers, plan optimal routes, and monitor delivery milestones with our advanced Transportation Management System.
        </p>
        
        <!-- Feature Cards -->
        <div class="d-flex flex-column gap-3 mt-4">
          <div class="d-flex align-items-center gap-3 p-3 bg-white border border-slate-100 rounded-4 shadow-xs">
            <div class="icon-circle bg-blue-soft text-primary">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-slate-800">Milestone Monitoring</h6>
              <span class="small text-slate-500">Track shipments from Draft to POD.</span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3 p-3 bg-white border border-slate-100 rounded-4 shadow-xs">
            <div class="icon-circle bg-emerald-soft text-success">
              <i class="bi bi-shield-check"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-slate-800">Secure Digital POD</h6>
              <span class="small text-slate-500">Direct recipient signature & photo proof.</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="z-3 text-slate-500 small">
        &copy; 2026 TMS Project. All rights reserved.
      </div>
    </div>

    <!-- Right Section: Login Form -->
    <div class="login-form-section d-flex align-items-center justify-content-center col-12 col-lg-5 p-4 p-sm-5 bg-slate-50">
      <div class="w-100 max-w-sm">
        
        <!-- Mobile Header (Visible on mobile/tablet) -->
        <div class="d-flex d-lg-none align-items-center gap-2 mb-4">
          <div class="logo-box-sm d-flex align-items-center justify-content-center overflow-hidden">
            <img src="/Logo.png" alt="Logo" class="w-100 h-100 object-fit-cover" />
          </div>
          <div>
            <h5 class="fw-bold mb-0 text-dark-blue">TMS Portal</h5>
            <span class="xx-small text-muted">Transportation Management System</span>
          </div>
        </div>

        <div class="bg-white p-4 p-sm-5 rounded-4 shadow-sm border border-slate-100">
          <div class="mb-4">
            <h3 class="fw-extrabold text-slate-900 mb-1">Sign In</h3>
            <p class="text-slate-500 small">Enter your credentials to access the portal</p>
          </div>

          <form @submit.prevent="handleLogin">
            <!-- Username Input -->
            <div class="mb-3">
              <label class="form-label text-slate-700 fw-semibold small mb-1.5">USERNAME</label>
              <div class="input-group">
                <span class="input-group-text bg-slate-50 text-slate-400 border-slate-200 rounded-start-3">
                  <i class="bi bi-person"></i>
                </span>
                <input
                  type="text"
                  class="form-control bg-white text-slate-800 border-slate-200 rounded-end-3"
                  v-model="username"
                  placeholder="Enter username"
                  required
                  :disabled="loading"
                />
              </div>
            </div>

            <!-- Password Input -->
            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-1.5">
                <label class="form-label text-slate-700 fw-semibold small mb-0">PASSWORD</label>
              </div>
              <div class="input-group">
                <span class="input-group-text bg-slate-50 text-slate-400 border-slate-200 rounded-start-3">
                  <i class="bi bi-lock"></i>
                </span>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  class="form-control bg-white text-slate-800 border-slate-200 border-end-0"
                  v-model="password"
                  placeholder="••••••••"
                  required
                  :disabled="loading"
                />
                <button
                  type="button"
                  class="input-group-text bg-white text-slate-500 border-slate-200 rounded-end-3 border-start-0"
                  @click="showPassword = !showPassword"
                  style="cursor: pointer;"
                  title="Toggle Password Visibility"
                >
                  <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                </button>
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              class="btn btn-primary w-100 py-2.5 fw-bold text-uppercase d-flex align-items-center justify-content-center rounded-3 shadow-sm"
              :disabled="loading"
            >
              <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <span>{{ loading ? 'Signing In...' : 'Sign In' }}</span>
            </button>
          </form>

          <div class="mt-4 text-center">
            <small class="text-slate-400">Please use your authorized account to sign in</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

const username = ref('');
const password = ref('');
const showPassword = ref(false);
const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();
const loading = ref(false);

const handleLogin = async () => {
  loading.value = true;
  try {
    const success = await authStore.login(username.value, password.value);
    if (success) {
      toast.success('Login berhasil! Selamat datang kembali.');
      router.push({ name: 'Dashboard' });
    } else {
      toast.error(authStore.error || 'Username atau password salah.');
    }
  } catch (err) {
    toast.error('Terjadi kesalahan sistem.');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');

.login-page {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  overflow-x: hidden;
  background-color: #f8fafc;
}

/* Visual Section Styling */
.login-visual-section {
  background-color: #f8fafc;
  border-right: 1px solid #e2e8f0;
}

/* Background grid styling */
.visual-bg-pattern {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  opacity: 0.45;
  background-image: 
    linear-gradient(#e2e8f0 1.5px, transparent 1.5px),
    linear-gradient(90deg, #e2e8f0 1.5px, transparent 1.5px);
  background-size: 30px 30px;
  z-index: 1;
  pointer-events: none;
}

/* Glowing graphic effect */
.visual-bg-pattern::before {
  content: '';
  position: absolute;
  width: 400px;
  height: 400px;
  top: 20%;
  left: 10%;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.12) 0%, rgba(255, 255, 255, 0) 70%);
  border-radius: 50%;
  pointer-events: none;
}

.logo-box {
  width: 50px;
  height: 50px;
  background-color: #e0f2fe;
  border: 1px solid #bae6fd;
  border-radius: 12px;
}

.logo-box-sm {
  width: 42px;
  height: 42px;
  background-color: #e0f2fe;
  border: 1px solid #bae6fd;
  border-radius: 10px;
}

.text-dark-blue {
  color: #0f172a;
}

.text-gradient {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 800;
}

.text-xs {
  font-size: 0.7rem;
}

.xx-small {
  font-size: 0.72rem;
}

.fw-extrabold {
  font-weight: 800;
}

.tracking-wide {
  letter-spacing: 0.05em;
}

.max-w-md {
  max-width: 540px;
}

.max-w-sm {
  max-width: 400px;
}

.bg-primary-soft {
  background-color: #eff6ff;
}

.bg-blue-soft {
  background-color: #eff6ff;
}

.bg-emerald-soft {
  background-color: #ecfdf5;
}

.icon-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.bg-slate-50 {
  background-color: #f8fafc;
}

.border-slate-100 {
  border-color: #f1f5f9 !important;
}

.border-slate-200 {
  border-color: #cbd5e1 !important;
}

.text-slate-400 {
  color: #94a3b8 !important;
}

.text-slate-500 {
  color: #64748b !important;
}

.text-slate-600 {
  color: #475569 !important;
}

.text-slate-700 {
  color: #334155 !important;
}

.text-slate-800 {
  color: #1e293b !important;
}

.text-slate-900 {
  color: #0f172a !important;
}

.shadow-xs {
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.shadow-sm {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05) !important;
}

/* Form inputs styling */
.form-control {
  border: 1px solid #cbd5e1;
  padding: 10px 14px;
  font-size: 14.5px;
  transition: all 0.2s ease;
}

.form-control:focus {
  border-color: #2563eb !important;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
  color: #0f172a !important;
}

.input-group-text {
  border-right: none;
  font-size: 1.1rem;
}

.form-control {
  border-left: none;
}

.btn-primary {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  border: none;
  font-size: 14.5px;
  transition: all 0.25s ease;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
}

.btn-primary:active {
  transform: translateY(0);
}
</style>
