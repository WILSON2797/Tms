import { defineStore } from 'pinia';
import axios from 'axios';

// Configure Axios base URL
axios.defaults.baseURL = '/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('access_token') || null,
    permissions: JSON.parse(localStorage.getItem('permissions')) || [],
    loading: false,
    error: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    hasRole: (state) => (role) => state.user?.role === role,
    hasPermission: (state) => (permission) => {
      if (state.user?.role === 'super-admin') return true;
      return state.permissions.includes(permission);
    }
  },
  actions: {
    async login(username, password) {
      this.loading = true;
      this.error = null;
      try {
        const response = await axios.post('/login', { username, password, client: 'web' });
        if (response.data.success) {
          const { access_token, user } = response.data.data;
          
          if (user.role === 'driver') {
            this.error = 'Username atau password salah.';
            return false;
          }

          this.token = access_token;
          this.user = user;
          
          localStorage.setItem('access_token', access_token);
          localStorage.setItem('user', JSON.stringify(user));
          localStorage.setItem('last_active_time', Date.now().toString());
          
          // Configure Axios Authorization Header
          axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;
          
          // Fetch complete profile with permissions
          await this.fetchUser();
          return true;
        }
      } catch (err) {
        this.error = err.response?.data?.message || 'Login gagal. Hubungi admin.';
        return false;
      } finally {
        this.loading = false;
      }
    },
    async fetchUser() {
      if (!this.token) return;
      
      // Ensure authorization header is set
      axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      
      try {
        const response = await axios.get('/me');
        if (response.data.success) {
          const { permissions, role, role_name, name, email, id } = response.data.data;
          this.user = { id, name, email, role, role_name };
          this.permissions = permissions;
          
          localStorage.setItem('user', JSON.stringify(this.user));
          localStorage.setItem('permissions', JSON.stringify(permissions));
        }
      } catch (err) {
        // Token might be invalid or expired
        this.logout();
      }
    },
    async logout() {
      try {
        if (this.token) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
          await axios.post('/logout');
        }
      } catch (err) {
        // Ignore errors during logout
      } finally {
        this.token = null;
        this.user = null;
        this.permissions = [];
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
        localStorage.removeItem('permissions');
        delete axios.defaults.headers.common['Authorization'];
      }
    }
  }
});
