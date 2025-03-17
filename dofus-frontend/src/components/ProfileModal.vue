<template>
  <transition name="fade">
    <div v-if="profileStore.isProfileOpen" class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-md">
      <div class="bg-gray-900 w-full max-w-md p-6 rounded-lg shadow-lg relative">

        <!-- Bouton de fermeture -->
        <button @click="profileStore.closeProfile" class="absolute top-2 right-2 text-gray-400 hover:text-white text-xl">
          ✖
        </button>

        <!-- Titre -->
        <h2 class="text-2xl font-semibold text-white text-center mb-4">Mon Profil</h2>

        <!-- Avatar -->
        <div class="flex flex-col items-center">
          <img :src="previewAvatar || user.avatar || '/default-avatar.png'" class="w-24 h-24 rounded-full border-2 border-gray-600 shadow-lg">
          <input type="file" ref="fileInput" @change="previewImage" accept="image/*" class="hidden">
          <button @click="$refs.fileInput.click()" class="mt-2 text-sm text-blue-400 hover:underline">Changer l'avatar</button>
        </div>

        <!-- Formulaire de modification -->
        <form @submit.prevent="updateProfile" class="mt-6 space-y-4">
          <div>
            <label class="block text-gray-300 text-sm font-medium">Pseudo</label>
            <input v-model="form.pseudo" type="text" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-3 py-2">
          </div>

          <div>
            <label class="block text-gray-300 text-sm font-medium">Nouveau mot de passe</label>
            <input v-model="form.newPassword" type="password" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-3 py-2">
          </div>

          <div>
            <label class="block text-gray-300 text-sm font-medium">Confirmer le mot de passe</label>
            <input v-model="form.confirmPassword" type="password" class="w-full bg-gray-800 text-white border border-gray-700 rounded px-3 py-2">
          </div>

          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded">
            Sauvegarder
          </button>
        </form>

        <button @click="deleteAccount" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 mt-4 rounded">
          Supprimer mon compte
        </button>

      </div>
    </div>
  </transition>
</template>

<script>
import { useProfileStore } from "@/store/useProfileStore.js";
import axios from 'axios';
import { mapState, mapActions } from "vuex";

export default {
  computed: {
    ...mapState("auth", ["user"]) // ✅ Mappe les données utilisateur depuis Vuex
  },
  setup() {
    const profileStore = useProfileStore();
    return { profileStore };
  },
  data() {
    return {
      form: {
        pseudo: '',
        newPassword: '',
        confirmPassword: '',
      },
      previewAvatar: null, // 🖼️ Stocke l'aperçu de l'avatar avant upload
      selectedFile: null, // 📂 Stocke le fichier sélectionné
    };
  },
  async mounted() {
    await this.fetchUser(); // ✅ Charge l'utilisateur au montage
    this.form.pseudo = this.user?.pseudo || "";
  },
  methods: {
    async fetchUser() {
      try {
        const response = await axios.get("http://127.0.0.1:8000/api/me", {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.user = response.data;
        this.form.pseudo = this.user.pseudo;
      } catch (error) {
        console.error("Erreur chargement utilisateur", error);
      }
    },

    // 🎯 Fonction pour afficher un aperçu de l'image
    previewImage(event) {
      const file = event.target.files[0];
      if (!file) return;

      this.selectedFile = file; // Stocke le fichier sélectionné

      // Utilisation de FileReader pour afficher l'aperçu
      const reader = new FileReader();
      reader.onload = (e) => {
        this.previewAvatar = e.target.result; // Met à jour l'aperçu
      };
      reader.readAsDataURL(file);

      // 📤 Auto-upload après sélection
      this.uploadAvatar();
    },

    async uploadAvatar() {
      if (!this.selectedFile) return;

      const formData = new FormData();
      formData.append("avatar", this.selectedFile);

      try {
        const response = await axios.post("http://127.0.0.1:8000/api/user/avatar", formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            "Content-Type": "multipart/form-data",
          },
        });

        // Mettre à jour l'avatar après upload réussi
        this.user.avatar = response.data.avatar;
        await this.fetchUser();
        this.previewAvatar = null; // Réinitialise l'aperçu après l'upload
      } catch (error) {
        console.error("Erreur lors du changement d'avatar", error);
      }
    },

    async updateProfile() {
      if (this.form.newPassword && this.form.newPassword !== this.form.confirmPassword) {
        alert("Les mots de passe ne correspondent pas !");
        return;
      }

      console.log("Données envoyées :", this.form); // 🔍 Vérifier les données

      try {
        await axios.put("http://127.0.0.1:8000/api/user/update", this.form, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });

        alert("Profil mis à jour !");
        await this.fetchUser();
        this.profileStore.closeProfile();
      } catch (error) {
        console.error("Erreur mise à jour profil", error.response?.data || error.message);
      }
    },

    async deleteAccount() {
      if (!confirm("Es-tu sûr de vouloir supprimer ton compte ? Cette action est irréversible !")) return;

      try {
        await axios.delete("http://127.0.0.1:8000/api/user/delete", {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });

        localStorage.removeItem("token");
        this.$router.push("/login");
      } catch (error) {
        console.error("Erreur suppression compte", error);
      }
    }
  }
};
</script>
