import { defineStore } from 'pinia';
import api from '@/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    token: localStorage.getItem('token') || null,
    sessionInterval: null as number | null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
  },
  actions: {
    async login(credentials: any) {
      const response = await api.post('/login', credentials);
      this.token = response.data.access_token;
      this.user = response.data.user;
      localStorage.setItem('token', this.token as string);
      localStorage.setItem('user', JSON.stringify(this.user));
    },
    async register(data: any) {
      const response = await api.post('/register', data);
      this.token = response.data.access_token;
      this.user = response.data.user;
      localStorage.setItem('token', this.token as string);
      localStorage.setItem('user', JSON.stringify(this.user));
    },
    async logout() {
      try {
        await api.post('/logout');
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      }
    },
    async fetchUser() {
      try {
        const response = await api.get('/me');
        this.user = response.data;
        localStorage.setItem('user', JSON.stringify(this.user));
      } catch (error) {
        this.stopSessionWatcher();
        this.logout();
      }
    },
    startSessionWatcher() {
      if (this.sessionInterval) return;
      
      // Check immediately, then every 60 seconds
      this.fetchUser();
      this.sessionInterval = window.setInterval(() => {
        if (this.token) {
          this.fetchUser();
        } else {
          this.stopSessionWatcher();
        }
      }, 60000);
    },
    stopSessionWatcher() {
      if (this.sessionInterval) {
        clearInterval(this.sessionInterval);
        this.sessionInterval = null;
      }
    },
  },
});
