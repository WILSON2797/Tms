<template>
  <div class="login-container d-flex align-items-center justify-content-center">
    <div class="login-glass-card p-5">
      <div class="text-center mb-4">
        <div class="logo-circle mb-3 mx-auto">
          <i class="bi bi-truck text-primary fs-1"></i>
        </div>
        <h2 class="fw-bold text-white mb-1">TMS Portal</h2>
        <p class="text-muted-custom">Transportation Management System</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label class="form-label text-light fs-7 small mb-1">USERNAME</label>
          <div class="input-group">
            <span class="input-group-text bg-dark-custom text-muted-custom border-secondary-custom">
              <i class="bi bi-person"></i>
            </span>
            <input
              type="text"
              class="form-control bg-dark-custom text-white border-secondary-custom"
              v-model="username"
              placeholder="Masukkan username"
              required
              :disabled="loading"
            />
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label text-light fs-7 small mb-1">PASSWORD</label>
          <div class="input-group">
            <span class="input-group-text bg-dark-custom text-muted-custom border-secondary-custom">
              <i class="bi bi-lock"></i>
            </span>
            <input
              type="password"
              class="form-control bg-dark-custom text-white border-secondary-custom"
              v-model="password"
              placeholder="••••••••"
              required
              :disabled="loading"
            />
          </div>
        </div>

        <button
          type="submit"
          class="btn btn-primary w-100 py-2.5 fw-bold text-uppercase d-flex align-items-center justify-content-center"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          <span>{{ loading ? 'Masuk...' : 'Sign In' }}</span>
        </button>
      </form>

      <div class="mt-4 text-center">
        <small class="text-muted-custom">Gunakan akun demo Anda untuk masuk</small>
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

.login-container {
  min-height: 100vh;
  width: 100vw;
  background: radial-gradient(circle at 10% 20%, rgb(18, 28, 48) 0%, rgb(10, 15, 26) 100.2%);
  margin: 0;
  padding: 0;
  overflow: hidden;
}

.login-glass-card {
  width: 100%;
  max-width: 440px;
  background: rgba(20, 30, 48, 0.6);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
}

.logo-circle {
  width: 70px;
  height: 70px;
  background: rgba(13, 110, 253, 0.15);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(13, 110, 253, 0.25);
  box-shadow: 0 0 20px rgba(13, 110, 253, 0.2);
}

.text-muted-custom {
  color: #8c98a5 !important;
}

.bg-dark-custom {
  background-color: rgba(10, 15, 26, 0.6) !important;
}

.border-secondary-custom {
  border-color: rgba(255, 255, 255, 0.08) !important;
}

.form-control:focus {
  background-color: rgba(10, 15, 26, 0.8) !important;
  border-color: #0d6efd !important;
  color: #fff !important;
  box-shadow: 0 0 10px rgba(13, 110, 253, 0.25) !important;
}

.form-control::placeholder {
  color: #4a545c;
}

.btn-primary {
  background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
  border: none;
  box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(13, 110, 253, 0.45);
}

.btn-primary:active {
  transform: translateY(0);
}
</style>
