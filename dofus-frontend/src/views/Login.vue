<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-900">
    <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-2xl font-semibold text-white text-center mb-6">Connexion</h2>
      <form @submit.prevent="login">
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium">Nom d'utilisateur</label>
          <input v-model="username" type="text" class="w-full bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500" required />
        </div>
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium">Mot de passe</label>
          <input v-model="password" type="password" class="w-full bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500" required />
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-600">
          Se connecter
        </button>
      </form>
      <p v-if="errorMessage" class="text-red-500 text-sm mb-4">{{ errorMessage }}</p>
      <p class="text-gray-400 text-sm text-center mt-4">
        Pas encore inscrit ?
        <router-link to="/register" class="text-blue-400 hover:underline">Inscrivez-vous</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import {useRouter} from 'vue-router';
import {useStore} from 'vuex';
import {ref} from "vue";

export default {
  setup() {
    const username = ref('');
    const password = ref('');
    const errorMessage = ref('');
    const router = useRouter();
    const store = useStore();

    const login = async () => {
      try {
        const response = await axios.post('http://127.0.0.1:8000/api/login', {
          username: username.value,
          password: password.value
        });

        const token = response.data.token;
        localStorage.setItem('token', token);

        // Récupérer les informations utilisateur après connexion
        const userResponse = await axios.get('http://127.0.0.1:8000/api/me', {
          headers: {Authorization: `Bearer ${token}`}
        });

        store.commit('auth/setUser', userResponse.data);
        store.commit('auth/setToken', token);

        router.push('/admin'); // Redirection vers le panneau admin
      } catch (error) {
        errorMessage.value = "Identifiants incorrects !";
      }
    };

    return {username, password, login, errorMessage};
  }
};
</script>
