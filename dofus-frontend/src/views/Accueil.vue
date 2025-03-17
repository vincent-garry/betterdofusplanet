<template>
  <div class="min-h-screen bg-gray-900 text-white relative">
    <!-- ✅ Navbar avec z-50 et position fixed pour toujours rester visible -->

    <!-- Background Image avec gradient descendu -->
    <div class="relative h-[90vh] overflow-hidden mt-0 pointer-events-none">
      <!-- Dégradé optimisé pour correspondre à la maquette -->
      <div class="absolute inset-0 bg-gradient-to-b from-transparent via-gray-900/75 to-gray-900 z-10"></div>
      <img src="@/components/images/dofus.jpg" alt="Background" class="w-full h-full object-cover object-top">
    </div>

    <!-- Contenu principal positionné par-dessus le background, mais sous la navbar -->
    <div class="absolute top-0 inset-x-0 z-20 pt-20">
      <div class="p-6 mt-4">
        <h1 class="text-5xl font-bold text-center mb-10 pt-4 text-white drop-shadow-lg">Dofus Primordiaux</h1>

        <!-- Section Dofus Primordiaux -->
        <div class="flex justify-center">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl">
            <div v-for="dofus in primordialDofus" :key="dofus.id" @click="goToDofus(dofus.id)"
                 class="cursor-pointer bg-gray-800/80 backdrop-blur p-4 rounded-xl shadow-lg hover:scale-105 transition-transform relative flex flex-col items-center mx-auto">
              <div class="relative w-48 h-48 overflow-hidden rounded-md">
                <img :src="dofus.image" class="w-full h-full object-cover transition-transform duration-300 hover:scale-110" />
                <img v-if="dofus.icon" :src="dofus.icon" class="absolute bottom-2 left-2 w-10 h-10" alt="Icon Dofus" />
              </div>
              <h2 class="text-xl font-bold text-center mt-3 text-white">{{ dofus.name }}</h2>
              <div class="flex justify-center items-center space-x-2 text-gray-300 text-sm mt-2">
                <p class="font-medium">{{ dofus.questCount }} quêtes</p>
                <img src="https://dofusplanet.fr/img/icons/quest_book2.png" alt="icon de quête" class="w-6 h-6">
                <p class="font-medium">{{ dofus.achievementCount }} succès</p>
                <img src="https://dofusplanet.fr/img/icons/achievement.png" alt="icone de succès" class="w-6 h-6">
              </div>
            </div>
          </div>
        </div>

        <h2 class="text-4xl font-bold text-center my-12 text-white drop-shadow-lg">Dofus Secondaires</h2>

        <!-- Section Dofus Secondaires -->
        <div class="flex justify-center">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 max-w-5xl">
            <div v-for="dofus in secondaryDofus" :key="dofus.id" @click="goToDofus(dofus.id)"
                 class="cursor-pointer bg-gray-800/80 backdrop-blur p-3 rounded-xl shadow-lg hover:scale-105 transition-transform relative flex flex-col items-center mx-auto">
              <div class="relative w-24 h-24 overflow-hidden rounded-md">
                <img :src="dofus.image" class="w-full h-full object-cover transition-transform duration-300 hover:scale-110" />
                <img v-if="dofus.icon" :src="dofus.icon" class="absolute bottom-2 left-2 w-8 h-8" alt="Icon Dofus" />
              </div>
              <h2 class="text-base font-bold text-center mt-2 text-white">{{ dofus.name }}</h2>
              <div class="flex justify-center items-center space-x-2 text-gray-300 text-xs mt-1">
                <p class="font-medium">{{ dofus.questCount }} quêtes</p>
                <img src="https://dofusplanet.fr/img/icons/quest_book2.png" alt="icon de quête" class="w-5 h-5">
                <p class="font-medium">{{ dofus.achievementCount }} succès</p>
                <img src="https://dofusplanet.fr/img/icons/achievement.png" alt="icone de succès" class="w-5 h-5">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Navbar from '@/components/NavBar.vue'

export default {
  components: {
    Navbar
  },
  data() {
    return {
      dofusList: [],
      dropdownOpen: false,
      apiUrl: 'http://127.0.0.1:8000/api/dofus'
    };
  },
  computed: {
    primordialDofus() {
      return this.dofusList.filter(d => d.type === 'primordial');
    },
    secondaryDofus() {
      return this.dofusList.filter(d => d.type === 'secondary');
    }
  },
  async mounted() {
    await this.fetchDofus();
  },
  methods: {
    async fetchDofus() {
      try {
        const response = await axios.get(this.apiUrl);
        this.dofusList = response.data;
      } catch (error) {
        console.error("Erreur chargement Dofus", error.response ? error.response.data : error.message);
      }
    },
    goToDofus(id) {
      this.$router.push(`/dofus/${id}`);
    },
    toggleDropdown() {
      this.dropdownOpen = !this.dropdownOpen;
    }
  }
};
</script>

<style scoped>
/* Animation au survol */
div.cursor-pointer:hover {
  transition: transform 0.3s ease-in-out;
  transform: scale(1.05);
}
</style>