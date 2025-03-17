<template>
  <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg transition-transform duration-300 hover:transform hover:scale-105">
    <div class="relative">
      <!-- Image du Dofus -->
      <img :src="dofus.image" :alt="dofus.name" class="w-full h-36 object-cover">

      <!-- Icône du Dofus -->
      <div class="absolute bottom-0 left-4 -mb-4 bg-gray-800 rounded-full p-1 border-2 border-gray-800 shadow-md">
        <div :class="`bg-${dofus.color} rounded-full h-8 w-8 flex items-center justify-center`">
          <img :src="dofus.iconImage || dofus.image" :alt="dofus.name" class="h-6 w-6">
        </div>
      </div>
    </div>

    <div class="px-4 py-4 pt-6">
      <!-- Nom du Dofus -->
      <h3 class="text-lg font-semibold">{{ dofus.name }}</h3>

      <div v-if="dofus.status" class="text-sm text-gray-400 mt-1">
        {{ dofus.status }}
      </div>

      <div v-else class="flex flex-col mt-1">
        <div class="text-sm text-gray-400">{{ dofus.quetes }} quêtes</div>
        <div class="text-sm text-gray-400">{{ dofus.succes }} succès</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DofusCard',
  props: {
    dofus: {
      type: Object,
      required: true
    }
  }
}
</script>