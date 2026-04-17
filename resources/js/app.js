import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import './bootstrap';

axios.defaults.baseURL = window.location.origin;

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

axios.defaults.headers.common['Accept'] = 'application/json';

const publicPaths = ['/login', '/register', '/auth/google/callback'];
const hasToken = Boolean(localStorage.getItem('token'));
if (!hasToken && !publicPaths.includes(window.location.pathname)) {
  window.location.replace(`/login?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`);
}

const app = createApp(App);
app.use(router);
app.mount('#app');