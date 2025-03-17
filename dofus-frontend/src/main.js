import { createPinia } from 'pinia';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import store from './store'; // Import Vuex
import './assets/main.css'; // Import TailwindCSS

const app = createApp(App);
app.use(router);
app.use(store); // Ajout de Vuex
app.use(createPinia());
app.mount('#app');
