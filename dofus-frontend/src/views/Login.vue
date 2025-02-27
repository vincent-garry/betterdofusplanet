<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-2xl font-bold text-center mb-4">Connexion</h2>
      <form @submit.prevent="login">
        <div class="mb-4">
          <label class="block text-gray-700">Nom d'utilisateur</label>
          <input v-model="username" type="text" class="w-full p-2 border rounded-lg" required />
        </div>
        <div class="mb-4">
          <label class="block text-gray-700">Mot de passe</label>
          <input v-model="password" type="password" class="w-full p-2 border rounded-lg" required />
        </div>
        <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-lg hover:bg-blue-600">
          Se connecter
        </button>
      </form>
      <p v-if="errorMessage" class="text-red-500 mt-2 text-center">{{ errorMessage }}</p>
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
