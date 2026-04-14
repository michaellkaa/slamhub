import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import './bootstrap';
import { registerSW } from 'virtual:pwa-register'

// Set base URL for all requests (dev/prod safe)
axios.defaults.baseURL = window.location.origin;

// Include cookies (required for Sanctum SPA)
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

axios.defaults.headers.common['Accept'] = 'application/json';
// Example: you can also set Authorization or any other header
// axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

const app = createApp(App);
app.use(router);
app.mount('#app');

registerSW({ immediate: true })