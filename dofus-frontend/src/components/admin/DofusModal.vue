<template>
  <!-- Fond semi-transparent avec flou -->
  <div class="fixed inset-0 flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900 bg-opacity-20 backdrop-blur-lg z-10"></div>

    <!-- Contenu du modal -->
    <div class="relative bg-white p-6 rounded-lg shadow-lg w-96 z-50">
      <h2 class="text-xl font-semibold mb-4">{{ modalTitle }}</h2>

      <div v-if="type !== 'delete'">
        <label class="block mb-2">Nom</label>
        <input v-model="formData.name" class="border p-2 rounded w-full mb-2" />

        <label class="block mb-2">Description</label>
        <textarea v-model="formData.description" class="border p-2 rounded w-full mb-2"></textarea>
      </div>

      <p v-if="type === 'delete'" class="text-red-500">Voulez-vous vraiment supprimer ce Dofus ?</p>

      <div class="flex justify-end mt-4 space-x-2">
        <button @click="$emit('close')" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</button>
        <button @click="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Confirmer</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["type", "dofus"],
  data() {
    return { formData: { name: this.dofus?.name || "", description: this.dofus?.description || "" } };
  },
  computed: {
    modalTitle() {
      return this.type === "add" ? "Ajouter un Dofus" : this.type === "edit" ? "Modifier un Dofus" : "Supprimer un Dofus";
    }
  },
  methods: {
    submit() {
      this.$emit('refresh');
      this.$emit('close');
    }
  }
};
</script>

<style scoped>
/* Forcer le flou et assurer le bon affichage */
.absolute {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px); /* Compatibilité Safari */
}
</style>
