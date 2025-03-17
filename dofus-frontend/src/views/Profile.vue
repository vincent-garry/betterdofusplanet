<template>
  <div v-if="user" class="min-h-screen bg-gray-900 text-white flex justify-center items-center p-6">
    <div class="bg-gray-800 shadow-lg rounded-lg p-6 max-w-lg w-full">
      <h2 class="text-2xl font-bold text-center mb-4">Mon Profil</h2>

      <!-- Avatar -->
      <div class="flex flex-col items-center mb-6">
        <div class="relative w-24 h-24 rounded-full overflow-hidden border-4 border-gray-600">
          <img :src="user.avatar || defaultAvatar" alt="Profil" class="w-full h-full object-cover">
          <label for="avatar-upload" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 cursor-pointer hover:bg-opacity-75 transition">
            📷
          </label>
          <input id="avatar-upload" type="file" accept="image/*" class="hidden" @change="uploadAvatar">
        </div>
        <p class="text-sm text-gray-400 mt-2">Cliquez sur l'avatar pour changer l'image</p>
      </div>

      <!-- Formulaire -->
      <div class="space-y-4">
        <!-- Pseudo -->
        <div>
          <label class="block text-gray-300 text-sm mb-1">Pseudo</label>
          <input v-model="user.username" type="text" class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:ring focus:ring-blue-500 outline-none">
        </div>

        <!-- Mot de passe actuel -->
        <div>
          <label class="block text-gray-300 text-sm mb-1">Mot de passe actuel</label>
          <input v-model="passwords.current" type="password" class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:ring focus:ring-blue-500 outline-none">
        </div>

        <!-- Nouveau mot de passe -->
        <div>
          <label class="block text-gray-300 text-sm mb-1">Nouveau mot de passe</label>
          <input v-model="passwords.new" type="password" class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:ring focus:ring-blue-500 outline-none">
        </div>

        <!-- Confirmation mot de passe -->
        <div>
          <label class="block text-gray-300 text-sm mb-1">Confirmer le nouveau mot de passe</label>
          <input v-model="passwords.confirm" type="password" class="w-full p-2 bg-gray-700 rounded border border-gray-600 focus:ring focus:ring-blue-500 outline-none">
        </div>

        <!-- Bouton de sauvegarde -->
        <button @click="saveProfile"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded transition">
          Sauvegarder
        </button>
      </div>
    </div>
  </div>

  <!-- Redirection si pas d'utilisateur connecté -->
  <div v-else class="min-h-screen flex items-center justify-center bg-gray-900 text-white">
    <p class="text-xl">Vous devez être connecté pour accéder à cette page.</p>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      user: null, // L'utilisateur connecté
      passwords: {
        current: "",
        new: "",
        confirm: ""
      },
      defaultAvatar: "https://via.placeholder.com/100" // Image par défaut
    };
  },
  async mounted() {
    await this.fetchUserProfile();
  },
  methods: {
    async fetchUserProfile() {
      try {
        const response = await axios.get("http://127.0.0.1:8000/api/user", {
          headers: {Authorization: `Bearer ${localStorage.getItem('token')}`}
        });
        this.user = response.data;
      } catch (error) {
        console.error("Erreur récupération du profil", error);
      }
    },
    async uploadAvatar(event) {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append("avatar", file);

      try {
        const response = await axios.post("http://127.0.0.1:8000/api/user/avatar", formData, {
          headers: {Authorization: `Bearer ${localStorage.getItem('token')}`}
        });
        this.user.avatar = response.data.avatar;
      } catch (error) {
        console.error("Erreur upload avatar", error);
      }
    },
    async saveProfile() {
      try {
        await axios.put("http://127.0.0.1:8000/api/user/update", {
          username: this.user.username,
          current_password: this.passwords.current,
          new_password: this.passwords.new,
          confirm_password: this.passwords.confirm
        }, {
          headers: {Authorization: `Bearer ${localStorage.getItem('token')}`}
        });

        alert("Profil mis à jour !");
      } catch (error) {
        console.error("Erreur mise à jour du profil", error);
      }
    }
  }
};
</script>
