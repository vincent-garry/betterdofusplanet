<template>
  <AdminLayout>
    <div class="p-6">
      <h1 class="text-3xl font-bold mb-6 text-gray-800">Gestion des Dofus</h1>

      <!-- Bouton d'ajout -->
      <button @click="openModal('add')" class="bg-purple-600 text-white px-4 py-2 rounded flex items-center">
        <span class="text-lg">+</span> <span class="ml-2">Nouveau Dofus</span>
      </button>

      <!-- Liste des Dofus -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
        <div v-for="dofus in dofusList" :key="dofus.id" class="relative bg-gray-800 shadow-lg rounded-lg p-4 text-white">

          <!-- Image du Dofus -->
          <img v-if="dofus.image" :src="dofus.image" alt="Dofus Image"
               class="w-full h-40 object-cover rounded-lg mb-4">

          <!-- Titre et niveau -->
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">{{ dofus.name }}</h2>
            <span class="bg-gray-700 text-white px-2 py-1 rounded text-sm">Niveau {{ dofus.level }}</span>
          </div>

          <!-- Description -->
          <p class="text-gray-300 text-sm mt-2">{{ dofus.description }}</p>

          <!-- Icône en bas à gauche -->
          <img v-if="dofus.icon" :src="dofus.icon" alt="Dofus Icon" class="w-10 h-10 absolute bottom-2 left-2">

          <!-- Menu contextuel -->
          <div class="absolute top-3 right-3">
            <button @click.stop="toggleMenu(dofus.id)" class="text-gray-400 hover:text-gray-100">
              &#x22EE; <!-- Menu "..." -->
            </button>

            <div v-if="activeMenu === dofus.id" class="absolute right-0 mt-2 w-40 bg-gray-900 shadow-lg rounded-lg p-2 z-50">
              <button @click="openModal('edit', dofus)" class="block text-gray-100 hover:bg-gray-800 w-full text-left px-4 py-2">Modifier</button>
              <button @click="openModal('delete', dofus)" class="block text-red-400 hover:bg-gray-800 w-full text-left px-4 py-2">Supprimer</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AdminLayout>

  <!-- MODAL en dehors de AdminLayout -->
  <teleport to="body">
    <div v-if="modalOpen" class="fixed inset-0 flex items-center justify-center bg-opacity-40 backdrop-blur-md z-50"
         @click.self="modalOpen = false">
      <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-semibold mb-4">{{ modalTitle }}</h2>

        <div v-if="modalType !== 'delete'">
          <label class="block mb-2">Nom</label>
          <input v-model="modalData.name" class="border p-2 rounded w-full mb-2" />

          <label class="block mb-2">Description</label>
          <textarea v-model="modalData.description" class="border p-2 rounded w-full mb-2"></textarea>

          <label class="block mb-2">Niveau</label>
          <input v-model.number="modalData.level" type="number" class="border p-2 rounded w-full mb-2" />

          <label class="block mb-2">URL Image</label>
          <input v-model="modalData.image" type="text" class="border p-2 rounded w-full mb-2" />

          <label class="block mb-2">URL Icon</label>
          <input v-model="modalData.icon" type="text" class="border p-2 rounded w-full mb-2" />

          <label class="block mb-2">Type de dofus</label>
          <select v-model="modalData.type" class="border p-2 rounded w-full">
            <option value="primordial">Primordial</option>
            <option value="secondary">Secondaire</option>
          </select>

          <label class="block mb-2">Nombre de succès</label>
          <input v-model.number="modalData.achievementCount" type="number" class="border p-2 rounded w-full mb-2" />
        </div>

        <p v-if="modalType === 'delete'" class="text-red-500">Voulez-vous vraiment supprimer ce Dofus ?</p>

        <div class="flex justify-end mt-4 space-x-2">
          <button @click="modalOpen = false" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</button>
          <button @click="submitModal" class="bg-purple-600 text-white px-4 py-2 rounded">Confirmer</button>
        </div>
      </div>
    </div>
  </teleport>
</template>
<script>
import AdminLayout from '../../layouts/AdminLayout.vue';
import axios from 'axios';

export default {
  components: { AdminLayout },
  data() {
    return {
      dofusList: [],
      modalOpen: false,
      modalType: null,
      modalData: { name: "", description: "", image: "", level: 1, type: "", achievementCount: 1, icon: "" },
      activeMenu: null, // Stocke l'ID du Dofus dont le menu contextuel est ouvert
      apiUrl: "http://127.0.0.1:8000/api/dofus"
    };
  },
  async mounted() {
    await this.fetchDofuses();
    document.addEventListener('click', this.closeMenu);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeMenu);
  },
  computed: {
    modalTitle() {
      return this.modalType === "add"
          ? "Ajouter un Dofus"
          : this.modalType === "edit"
              ? "Modifier un Dofus"
              : "Supprimer un Dofus";
    }
  },
  methods: {
    async fetchDofuses() {
      try {
        const response = await axios.get(this.apiUrl, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.dofusList = response.data;
      } catch (error) {
        console.error('Erreur chargement Dofus', error);
      }
    },

    openModal(type, dofus = { name: "", description: "", image: "", level: 1, type: "", achievementCount: 1, icon: "" }) {
      this.modalType = type;
      this.modalData = { ...dofus };
      this.modalOpen = true;
    },

    async submitModal() {
      try {
        const data = {
          name: this.modalData.name,
          description: this.modalData.description,
          image: this.modalData.image,
          level: Number(this.modalData.level),
          type: this.modalData.type,
          achievementCount: this.modalData.achievementCount,
          icon: this.modalData.icon,
        };

        let response;
        if (this.modalType === "add") {
          response = await axios.post(this.apiUrl, data, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
          console.log(response);
        } else if (this.modalType === "edit") {
          response = await axios.put(`${this.apiUrl}/${this.modalData.id}`, data, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
        } else if (this.modalType === "delete") {
          response = await axios.delete(`${this.apiUrl}/${this.modalData.id}`, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
        }

        console.log("Réponse API :", response.data);
        this.modalOpen = false;
        await this.fetchDofuses();
      } catch (error) {
        console.error('Erreur lors de l’opération', error.response ? error.response.data : error.message);
      }
    },

    toggleMenu(id) {
      this.activeMenu = this.activeMenu === id ? null : id;
    },

    closeMenu(event) {
      if (!event.target.closest(".relative")) {
        this.activeMenu = null;
      }
    }
  }
};
</script>
