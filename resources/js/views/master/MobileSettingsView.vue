<template>
  <div class="mobile-settings-view">
    <div class="card bg-dark-card border-card p-4 rounded-4 shadow-lg text-gray-900 dark:text-white">
      <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary-custom">
        <div>
          <h4 class="fw-bold text-gray-900 mb-1">
            <i class="bi bi-phone text-primary me-2"></i>
            Pengaturan Aplikasi Mobile
          </h4>
          <p class="text-muted small mb-0">Kelola pengaturan versi aplikasi supir untuk force update atau optional update secara real-time.</p>
        </div>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary mb-2" role="status"></div>
        <p class="text-muted small">Memuat pengaturan...</p>
      </div>

      <form v-else @submit.prevent="handleSubmit">
        <div class="row g-4">
          <!-- Latest Version -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small fw-bold mb-1">VERSI APLIKASI TERBARU (LATEST VERSION)</label>
            <input 
              type="text" 
              class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
              v-model="form.mobile_latest_version" 
              placeholder="Contoh: 2.0.0" 
              required 
            />
            <div class="form-text text-muted" style="font-size: 11.5px;">
              Versi terbaru yang tersedia di server. Jika aplikasi supir di bawah versi ini, sistem akan menyarankan update opsional (*Soft Update*).
            </div>
          </div>

          <!-- Minimum Version -->
          <div class="col-12 col-md-6">
            <label class="form-label text-muted small fw-bold mb-1">VERSI APLIKASI MINIMAL (MINIMUM VERSION)</label>
            <input 
              type="text" 
              class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
              v-model="form.mobile_minimum_version" 
              placeholder="Contoh: 1.1.0" 
              required 
            />
            <div class="form-text text-muted" style="font-size: 11.5px;">
              Versi minimal wajib. Jika aplikasi supir di bawah versi ini, supir **dipaksa** update (*Force Update*) sebelum bisa masuk ke aplikasi.
            </div>
          </div>

          <!-- Download URL -->
          <div class="col-12">
            <label class="form-label text-muted small fw-bold mb-1">LINK DOWNLOAD APK TERBARU (DOWNLOAD URL)</label>
            <input 
              type="url" 
              class="form-control bg-dark-custom text-gray-900 dark:text-white border-secondary-custom" 
              v-model="form.mobile_download_url" 
              placeholder="Contoh: https://domainanda.com/tms/downloads/app.apk" 
              required 
            />
            <div class="form-text text-muted" style="font-size: 11.5px;">
              Tautan unduhan langsung file APK yang akan dibuka di browser handphone supir ketika mereka menekan tombol "Update Sekarang".
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-4 pt-3 border-top border-secondary-custom">
          <button type="submit" class="btn btn-primary px-4" :disabled="submitLoading">
            <span v-if="submitLoading" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i class="bi bi-save me-1" v-else></i>
            Simpan Pengaturan
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const loading = ref(false);
const submitLoading = ref(false);

const form = reactive({
  mobile_latest_version: '',
  mobile_minimum_version: '',
  mobile_download_url: ''
});

onMounted(() => {
  fetchSettings();
});

const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/settings/mobile');
    if (response.data.success) {
      const data = response.data.data;
      form.mobile_latest_version = data.mobile_latest_version || '1.0.0';
      form.mobile_minimum_version = data.mobile_minimum_version || '1.0.0';
      form.mobile_download_url = data.mobile_download_url || '';
    }
  } catch (err) {
    toast.error('Gagal memuat pengaturan versi aplikasi.');
  } finally {
    loading.value = false;
  }
};

const handleSubmit = async () => {
  submitLoading.value = true;
  try {
    const response = await axios.put('/settings/mobile', form);
    if (response.data.success) {
      toast.success(response.data.message || 'Pengaturan berhasil diperbarui!');
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Gagal menyimpan pengaturan.');
  } finally {
    submitLoading.value = false;
  }
};
</script>

<style scoped>
.mobile-settings-view {
  max-width: 900px;
  margin: 0 auto;
}
</style>
