<template>
  <nav class="bg-gray-900 text-white shadow-md">
    <div class="container mx-auto px-4 py-2 flex items-center justify-between">
      <!-- Logo -->
      <div class="flex items-center">
        <router-link to="/" class="flex items-center">
          <!--<img src="/assets/logo-dofusplanet.svg" alt="DofusPlanet" class="h-10" />-->
        </router-link>
      </div>

      <!-- Navigation Links -->
      <div class="hidden md:flex space-x-6">
        <router-link to="/" class="py-2 hover:text-gray-300 transition duration-150">Accueil</router-link>
        <router-link to="/quetes" class="py-2 hover:text-gray-300 transition duration-150">Quêtes</router-link>
        <router-link to="/donjons" class="py-2 hover:text-gray-300 transition duration-150">Donjons</router-link>
      </div>

      <!-- User Profile -->
      <div class="relative">
        <button @click="toggleDropdown" class="flex items-center focus:outline-none">
          <img :src="userAvatar" alt="Avatar" class="h-8 w-8 rounded-full border-2 border-blue-400" />
        </button>

        <!-- Dropdown Menu -->
        <div v-if="isDropdownOpen" class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-md shadow-lg py-1 z-10">
          <router-link to="/profil" class="block px-4 py-2 text-sm hover:bg-gray-700">Profil</router-link>
          <router-link to="/personnages" class="block px-4 py-2 text-sm hover:bg-gray-700">Personnages</router-link>
          <button @click="logout" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-700">Se déconnecter</button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'NavBar',
  data() {
    return {
      isDropdownOpen: false,
      userAvatar: '/assets/avatar.png', // Placeholder, replace with actual user avatar
    }
  },
  methods: {
    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen
    },
    logout() {
      // Implement logout logic
      this.isDropdownOpen = false
      // Redirect to login page or homepage
      this.$router.push('/login')
    }
  },
  mounted() {
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!this.$el.contains(e.target)) {
        this.isDropdownOpen = false
      }
    })
  }
}
</script>