import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import './bootstrap';
import '../css/theme.css';
import { initTheme } from './composables/useTheme';

initTheme();

axios.defaults.baseURL = window.location.origin;
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common['Accept'] = 'application/json';

const publicPaths = ['/login', '/register', '/verify-email', '/auth/google/callback'];
const token = localStorage.getItem('token');

if (token) {
  axios.defaults.headers.common.Authorization = `Bearer ${token}`;
}

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    const isLoginRoute = router.currentRoute.value?.name === 'login';

    if (status === 401 && !isLoginRoute) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      delete axios.defaults.headers.common.Authorization;

      router.push({
        name: 'login',
        query: { redirect: window.location.pathname + window.location.search },
      });
    }

    return Promise.reject(error);
  }
);

const hasToken = Boolean(token);
if (!hasToken && !publicPaths.includes(window.location.pathname)) {
  window.location.replace(`/login?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`);
}

const app = createApp(App);
app.use(router);
app.mount('#app');
