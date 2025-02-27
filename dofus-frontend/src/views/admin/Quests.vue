<template>
  <AdminLayout>
    <div class="p-6">
      <h1 class="text-3xl font-bold mb-6">Gestion des Quêtes</h1>

      <!-- Barre de recherche -->
      <input v-model="search" placeholder="Rechercher une quête..." class="border p-2 rounded w-full mb-4" />

      <!-- Bouton d'ajout -->
      <button @click="openModal('add')" class="bg-green-600 text-white px-4 py-2 rounded">
        + Nouvelle Quête
      </button>

      <!-- Tableau des quêtes -->
      <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-lg rounded-lg">
          <thead class="bg-gray-800 text-white">
          <tr>
            <th @click="sortBy('id')" class="px-4 py-2 cursor-pointer">ID ⬆⬇</th>
            <th @click="sortBy('title')" class="px-4 py-2 cursor-pointer">Titre ⬆⬇</th>
            <th class="px-4 py-2">Description</th>
            <th class="px-4 py-2">Niveau</th>
            <th class="px-4 py-2">Dofus</th>
            <th class="px-4 py-2">Récompenses</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="quest in filteredQuests" :key="quest.id" class="border-b">
            <td class="px-4 py-2">{{ quest.id }}</td>
            <td class="px-4 py-2">{{ quest.title }}</td>
            <td class="px-4 py-2">{{ quest.description }}</td>
            <td class="px-4 py-2">{{ quest.level }}</td>
            <td class="px-4 py-2">{{ quest.dofus.name }}</td>
            <td class="px-4 py-2">
              <ul v-if="quest.rewards && quest.rewards.length">
                <li v-for="reward in quest.rewards" :key="reward.id">
                  {{ reward.name }} x{{ reward.quantity }}
                </li>
              </ul>
              <span v-else class="text-gray-400">Aucune récompense</span>
            </td>

            <td class="px-4 py-2">
              <button @click="openModal('edit', quest)" class="bg-yellow-500 text-white px-2 py-1 rounded mr-2">Modifier</button>
              <button @click="openModal('delete', quest)" class="bg-red-500 text-white px-2 py-1 rounded">Supprimer</button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL -->
    <teleport to="body">
      <div v-if="modalOpen" class="fixed inset-0 flex items-center justify-center bg-opacity-50 backdrop-blur-md">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
          <h2 class="text-xl font-semibold mb-4">{{ modalTitle }}</h2>

          <div v-if="modalType !== 'delete'">
            <label class="block mb-2">Titre</label>
            <input v-model="modalData.title" class="border p-2 rounded w-full mb-2" />

            <label class="block mb-2">Description</label>
            <textarea v-model="modalData.description" class="border p-2 rounded w-full mb-2"></textarea>

            <label class="block mb-2">Niveau</label>
            <input v-model.number="modalData.level" type="number" min="1" class="border p-2 rounded w-full mb-2" />

            <label class="block mb-2">Dofus associé</label>
            <select v-model="modalData.dofus_id" class="border p-2 rounded w-full">
              <option v-for="dofus in dofusList" :key="dofus.id" :value="dofus.id">
                {{ dofus.name }}
              </option>
            </select>

            <label class="block mb-2">Ordre</label>
            <input v-model.number="modalData.quest_ordre" type="number" min="1" class="border p-2 rounded w-full mb-2" />

            <!-- Gestion des récompenses -->
            <label class="block mt-4 mb-2">Récompenses</label>
            <div v-for="(reward, index) in modalData.rewards" :key="index" class="flex gap-2 items-center">
              <input v-model="reward.name" placeholder="Nom" class="border p-2 rounded w-full" />
              <input v-model.number="reward.quantity" type="number" min="1" class="border p-2 rounded w-24" />
              <button @click="removeReward(index)" class="bg-red-500 text-white px-2 py-1 rounded">✖</button>
            </div>
            <button @click="addReward" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded">+ Ajouter une récompense</button>
          </div>

          <p v-if="modalType === 'delete'" class="text-red-500">Voulez-vous vraiment supprimer cette quête ?</p>

          <div class="flex justify-end mt-4 space-x-2">
            <button @click="modalOpen = false" class="bg-gray-500 text-white px-4 py-2 rounded">Annuler</button>
            <button @click="submitModal" class="bg-blue-600 text-white px-4 py-2 rounded">Confirmer</button>
          </div>
        </div>
      </div>
    </teleport>
  </AdminLayout>
</template>

<script>
import AdminLayout from '../../layouts/AdminLayout.vue';
import axios from 'axios';

export default {
  components: {AdminLayout},
  data() {
    return {
      quests: [],
      dofusList: [],
      search: '',
      modalOpen: false,
      modalType: null,
      modalData: {title: '', description: '', level: 1, quest_order: 1, dofus_id: null, rewards: []},
      apiUrl: 'http://127.0.0.1:8000/api/quests',
      dofusApiUrl: 'http://127.0.0.1:8000/api/dofus'
    };
  },
  methods: {
    async fetchQuests() {
      try {
        const token = localStorage.getItem("token");
        if (!token) {
          console.error("Token JWT manquant !");
          return;
        }

        const response = await axios.get(this.apiUrl, {
          headers: { Authorization: `Bearer ${token}` },
        });

        console.log("✅ Réponse API des quêtes:", response.data); // Debug API

        this.quests = [...response.data];
        console.log("🎯 this.quests après assignation:", this.quests); // Vérifier si Vue.js met bien à jour
      } catch (error) {
        console.error("🚨 Erreur chargement Quêtes", error.response ? error.response.data : error.message);
      }
    },
    async fetchDofus() {
      try {
        const token = localStorage.getItem("token");
        if (!token) {
          console.error("Token JWT manquant !");
          return;
        }

        const response = await axios.get(this.dofusApiUrl, {
          headers: { Authorization: `Bearer ${token}` },
        });

        console.log("Réponse API des Dofus:", response.data); // DEBUG

        this.dofusList = response.data;
      } catch (error) {
        console.error("Erreur chargement Dofus", error.response ? error.response.data : error.message);
      }
    },
    openModal(type, quest = { title: "", description: "", level: 1, dofus_id: null, quest_order: 1, rewards: [] }) {
      this.modalType = type;

      // S'assurer que quest.rewards est toujours un tableau
      this.modalData = {
        ...quest,
        rewards: Array.isArray(quest.rewards) ? [...quest.rewards] : []
      };

      this.modalOpen = true;
    },
    addReward() {
      this.modalData.rewards.push({name: '', quantity: 1});
    },
    removeReward(index) {
      this.modalData.rewards.splice(index, 1);
    },
    async submitModal() {
      try {
        const data = {
          title: this.modalData.title,
          description: this.modalData.description,
          level: Number(this.modalData.level),
          dofus_id: this.modalData.dofus_id || null,
          quest_order: Number(this.modalData.quest_order) || 1, // 🛠 Correction ici
          rewards: this.modalData.rewards.map(r => ({
            name: r.name,
            quantity: Number(r.quantity) || 1
          })) || null
        };

        console.log("Données envoyées:", data); // DEBUG

        if (this.modalType === "add") {
          await axios.post(this.apiUrl, data, {
            headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
          });
        } else if (this.modalType === "edit") {
          await axios.put(`${this.apiUrl}/${this.modalData.id}`, data, {
            headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
          });
        } else if (this.modalType === "delete") {
          await axios.delete(`${this.apiUrl}/${this.modalData.id}`, {
            headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
          });
        }

        this.modalOpen = false;
        await this.fetchQuests();
      } catch (error) {
        console.error("Erreur lors de l’opération", error.response ? error.response.data : error.message);
      }
    }
  },
  mounted() {
    this.fetchQuests();
    this.fetchDofus();
    document.addEventListener('click', this.closeMenu);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeMenu);
  },
  computed: {
    filteredQuests() {
      console.log("📌 this.quests dans computed():", this.quests); // 🔥 DEBUG
      return [...this.quests]
          .filter(q => q.title.toLowerCase().includes(this.search.toLowerCase()))
          .sort((a, b) => (a[this.sortKey] > b[this.sortKey] ? this.sortOrder : -this.sortOrder));
    }
  },
};
</script>
