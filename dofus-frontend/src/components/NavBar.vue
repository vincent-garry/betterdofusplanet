<template>
  <nav class="fixed top-0 left-0 w-full bg-gray-800/80 backdrop-blur-md shadow-md z-[100]">
    <div class="container mx-auto flex justify-between items-center py-3 px-6">

      <!-- Logo -->
      <a href="/" class="text-white text-xl font-bold flex items-center space-x-2">
        <img src="https://www.better-stronger.com/hs-fs/hubfs/website/logos/clients/image1.png?width=180&name=image1.png" alt="Logo" class="w-7 h-7">
        <span>BetterDofusPlanet</span>
      </a>

      <!-- Si l'utilisateur est connecté, afficher son profil -->
      <div v-if="user" class="relative">
        <button @click="toggleDropdown" class="flex items-center space-x-3 focus:outline-none">
          <img :src="user?.avatar || '/default-avatar.png'" class="w-10 h-10 rounded-full border-2 border-gray-400 hover:border-white transition duration-300">
          <span class="text-white text-sm font-medium hidden sm:inline">{{ user?.pseudo || 'Utilisateur' }}</span>
        </button>

        <!-- Menu déroulant -->
        <transition name="fade">
          <div v-if="dropdownOpen" class="absolute right-0 mt-3 w-52 bg-gray-900 rounded-lg shadow-lg overflow-hidden z-50 border border-gray-700">
            <!-- Avatar + Nom -->
            <div class="flex items-center space-x-3 px-4 py-3 bg-gray-800 border-b border-gray-700">
              <img :src="user?.avatar || '/default-avatar.png'" class="w-10 h-10 rounded-full border-2 border-gray-400">
              <div>
                <p class="text-white font-semibold text-sm">{{ user.pseudo }}</p>
                <p class="text-gray-400 text-xs">Mon compte</p>
              </div>
            </div>

            <!-- Bouton Voir mon Profil -->
            <button @click="profileStore.openProfile()" class="flex items-center w-full px-4 py-3 text-gray-400 hover:bg-gray-700 hover:text-white transition duration-200 text-sm font-medium">
              <i class="fas fa-user-circle text-gray-400 mr-3"></i>
              <span class="flex-1 text-left">Voir mon profil</span>
            </button>

            <!-- Bouton Déconnexion -->
            <button @click="logout" class="flex items-center w-full px-4 py-3 text-red-400 hover:bg-red-700 hover:text-white transition duration-200 text-sm font-medium">
              <i class="fas fa-right-from-bracket mr-3"></i>
              <span class="flex-1 text-left">Se déconnecter</span>
            </button>
          </div>
        </transition>
      </div>

      <!-- Si l'utilisateur n'est PAS connecté, afficher les boutons Connexion & Inscription -->
      <div v-else class="flex space-x-4">
        <router-link to="/login" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded">
          Connexion
        </router-link>
        <router-link to="/register" class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2 px-4 rounded">
          Inscription
        </router-link>
      </div>

    </div>
  </nav>
</template>

<script>
import axios from 'axios';
import { useProfileStore } from "@/store/useProfileStore.js";
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
      dropdownOpen: false,
    };
  },
  async mounted() {
    await this.fetchUser();
  },
  methods: {
    ...mapActions("auth", ["fetchUser"]),
    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    },
    async logout() {
      try {
        await axios.post("http://127.0.0.1:8000/api/logout", {}, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        }).catch(() => {});

        localStorage.removeItem("token");
        this.$router.push("/login");
      } catch (error) {
        console.error("Erreur lors de la déconnexion", error);
      }
    }
  }
};
</script>

<style scoped>
/* Animation du menu */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease-in-out;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

/* Icônes */
i {
  font-size: 18px;
  vertical-align: middle;
  min-width: 24px;
  text-align: center;
}

/* Espacement des boutons */
a, button {
  display: flex;
  align-items: center;
  padding: 10px;
  border-radius: 4px;
}

a:hover, button:hover {
  background-color: rgba(255, 255, 255, 0.1);
}
</style>
