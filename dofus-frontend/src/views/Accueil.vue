<template>
  <div class="min-h-screen bg-gray-900 text-white">
    <!-- Background Image with Overlay -->
    <div class="relative">
      <div class="absolute inset-0 overflow-hidden">
        <img
            src="https://static.ankama.com/upload/backoffice/direct/2024-11-01/6c651c533460e8dd6bc4ffb72b4c1249.jpg"
            alt="Background"
            class="w-full h-full object-cover opacity-20"
        >
      </div>

      <!-- Content -->
      <div class="relative container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center my-8">DOFUS PRIMORDIAUX</h1>

        <!-- Decorative divider -->
        <div class="flex justify-center items-center mb-10">
          <div class="h-px w-4 bg-gray-500"></div>
          <div class="mx-2">
            <span class="inline-block h-2 w-2 bg-yellow-500 rounded-full"></span>
          </div>
          <div class="h-px w-4 bg-gray-500"></div>
        </div>

        <!-- Dofus Primordiaux Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
          <dofus-card
              v-for="dofus in dofusPrimordiaux"
              :key="dofus.id"
              :dofus="dofus"
          />
        </div>

        <!-- Dofus Secondaires Section -->
        <h2 class="text-xl font-semibold mb-4 border-b border-blue-400 pb-2 inline-block">Dofus Secondaires</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <dofus-card
              v-for="dofus in dofusSecondaires"
              :key="dofus.id"
              :dofus="dofus"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import DofusCard from '../components/DofusCard.vue'

export default {
  name: 'DofusPrimordiauxPage',
  components: {
    DofusCard
  },
  data() {
    return {
      dofusPrimordiaux: [
        {
          id: 1,
          name: 'Dofus Pourpre',
          image: '/dofus-pourpre.png',
          quetes: 18,
          succes: 1,
          color: 'red-500'
        },
        {
          id: 2,
          name: 'Dofus Émeraude',
          image: '/dofus-emeraude.png',
          quetes: 18,
          succes: 2,
          color: 'green-500'
        },
        {
          id: 3,
          name: 'Dofus Turquoise',
          image: '/dofus-turquoise.png',
          quetes: 21,
          succes: 1,
          color: 'blue-400'
        },
        {
          id: 4,
          name: 'Dofus Ébène',
          image: '/dofus-ebene.png',
          quetes: 53,
          succes: 4,
          color: 'purple-900'
        },
        {
          id: 5,
          name: 'Dofus Ivoire',
          image: '/dofus-ivoire.png',
          quetes: 105,
          succes: 8,
          color: 'yellow-100'
        },
        {
          id: 6,
          name: 'Dofus Ocre',
          image: '/dofus-ocre.png',
          quetes: 0,
          succes: 0,
          color: 'yellow-600',
          status: 'Bientôt disponible'
        }
      ],
      dofusSecondaires: [
        {
          id: 7,
          name: 'Dofus Cawotte',
          image: '/dofus-cawotte.png',
          quetes: 19,
          succes: 3,
          color: 'orange-400'
        },
        {
          id: 8,
          name: 'Dofus Vulbis',
          image: '/dofus-vulbis.png',
          quetes: 13,
          succes: 1,
          color: 'red-600'
        },
        {
          id: 9,
          name: 'Dofus des Glaces',
          image: '/dofus-glaces.png',
          quetes: 42,
          succes: 7,
          color: 'blue-200'
        },
        // Ajoutez les autres dofus secondaires ici
      ]
    }
  }
}
</script>