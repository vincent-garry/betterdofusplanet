<template>
  <AdminLayout>
    <div class="p-6">
      <h1 class="text-3xl font-bold mb-6">Gestion des Étapes de Quêtes</h1>

      <!-- Barre de recherche -->
      <input
          v-model="search"
          placeholder="Rechercher une quête ou une étape..."
          class="border p-2 rounded w-full mb-4"
      />

      <!-- Bouton d'ajout -->
      <button @click="openModal('add')" class="bg-green-600 text-white px-4 py-2 rounded">
        + Nouvelle Étape
      </button>

      <!-- Tableau des étapes -->
      <div class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white shadow-lg rounded-lg">
          <thead class="bg-gray-800 text-white">
          <tr>
            <th @click="sortBy('id')" class="px-4 py-2 cursor-pointer">ID ⬆⬇</th>
            <th class="px-4 py-2">Titre</th>
            <th class="px-4 py-2">Description</th>
            <th class="px-4 py-2">Position</th>
            <th class="px-4 py-2">Image</th>
            <th class="px-4 py-2">Ordre</th>
            <th class="px-4 py-2">Quête</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="step in filteredQuestSteps" :key="step.id" class="border-b">
            <td class="px-4 py-2">{{ step.id }}</td>
            <td class="px-4 py-2">{{ step.title }}</td>
            <td class="px-4 py-2">{{ step.description }}</td>
            <td class="px-4 py-2">{{ step.positionX }};{{ step.positionY }}</td>
            <td class="px-4 py-2">
              <img v-if="step.image" v-for="(img, index) in step.image.split(';')" :key="index"
                   :src="img.trim()" alt="Illustration"
                   class="w-32 h-32 object-cover rounded-lg border border-gray-600 shadow cursor-pointer"
                   @click.stop="openImageModal(img.trim())">
              <span v-else class="text-gray-400">Aucune image</span>
            </td>
            <td class="px-4 py-2">{{ step.step_order }}</td>
            <td class="px-4 py-2">{{ step.quest ? step.quest.title : 'Aucune quête' }}</td>
            <td class="px-4 py-2">
              <button @click="openModal('edit', step)" class="bg-yellow-500 text-white px-2 py-1 rounded mr-2">Modifier</button>
              <button @click="openModal('delete', step)" class="bg-red-500 text-white px-2 py-1 rounded">Supprimer</button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL -->
    <teleport to="body">
      <div v-if="modalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-md">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
          <h2 class="text-xl font-semibold mb-4">{{ modalTitle }}</h2>

          <div v-if="modalType !== 'delete'">
            <label class="block mb-2">Titre</label>
            <input v-model="modalData.title" class="border p-2 rounded w-full mb-2" />

            <label class="block mb-2">Description</label>
            <textarea v-model="modalData.description" class="border p-2 rounded w-full mb-2"></textarea>

            <label class="block mb-2">Position</label>
            <input v-model="modalData.position" placeholder="Format: [X;Y]" class="border p-2 rounded w-full mb-2" />

            <label class="block mb-2">Image (URL)</label>
            <input v-model="modalData.image" class="border p-2 rounded w-full mb-2" />

            <label class="block mb-2">Ordre</label>
            <input v-model.number="modalData.step_order" type="number" class="border p-2 rounded w-full mb-2" />

            <label class="block mb-2">Quête associée</label>
            <input v-model="searchQuest" @focus="showQuestList = true" @blur="hideQuestList" placeholder="Rechercher une quête..." class="border p-2 rounded w-full mb-2" />
            <ul v-if="showQuestList" class="bg-white border rounded shadow max-h-40 overflow-auto absolute w-full">
              <li v-for="quest in filteredQuests" :key="quest.id" @mousedown.prevent="selectQuest(quest)" class="p-2 cursor-pointer hover:bg-gray-200">
                {{ quest.title }}
              </li>
            </ul>
          </div>

          <p v-if="modalType === 'delete'" class="text-red-500">Voulez-vous vraiment supprimer cette étape ?</p>

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
  components: { AdminLayout },
  data() {
    return {
      questSteps: [],
      quests: [],
      search: '',
      searchQuest: '',
      showQuestList: false,
      modalOpen: false,
      modalType: null,
      modalData: { title: '', description: '', position: '', image: '', step_order: 1, quest_id: null },
      apiUrl: 'http://127.0.0.1:8000/api/quest-steps',
      questApiUrl: 'http://127.0.0.1:8000/api/quests'
    };
  },
  computed: {
    modalTitle() {
      return this.modalType === 'add' ? 'Ajouter une Étape' : this.modalType === 'edit' ? 'Modifier une Étape' : 'Supprimer une Étape';
    },
    filteredQuestSteps() {
      return this.questSteps.filter(step =>
          step.title.toLowerCase().includes(this.search.toLowerCase()) ||
          (step.quest && step.quest.title.toLowerCase().includes(this.search.toLowerCase()))
      );
    },
    filteredQuests() {
      return this.quests.filter(quest => quest.title.toLowerCase().includes(this.searchQuest.toLowerCase()));
    }
  },
  async mounted() {
    await this.fetchQuestSteps();
    await this.fetchQuests();
  },
  methods: {
    async fetchQuestSteps() {
      try {
        const response = await axios.get(this.apiUrl, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.questSteps = response.data.map(step => ({
          ...step,
          quest_id: step.quest ? step.quest.id : null // Assurer que quest_id est défini
        }));
      } catch (error) {
        console.error("Erreur chargement Étapes", error.response ? error.response.data : error.message);
      }
    },
    async fetchQuests() {
      try {
        const response = await axios.get(this.questApiUrl, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });
        this.quests = response.data;
      } catch (error) {
        console.error("Erreur chargement Quêtes", error.response ? error.response.data : error.message);
      }
    },
    openModal(type, step = { title: '', description: '', position: '', image: '', step_order: 1, quest_id: null }) {
      this.modalType = type;
      this.modalData = { ...step, position: step.positionX && step.positionY ? `[${step.positionX};${step.positionY}]` : '' };
      this.modalOpen = true;
    },
    async submitModal() {
      try {
        const [positionX, positionY] = this.modalData.position.replace(/\[|\]/g, '').split(';').map(Number);
        const data = {
          title: this.modalData.title,
          description: this.modalData.description,
          position: this.modalData.position,
          image: this.modalData.image,
          step_order: Number(this.modalData.step_order),
          quest_id: this.modalData.quest_id
        };

        if (this.modalType === "add") {
          await axios.post(this.apiUrl, data, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
        } else if (this.modalType === "edit") {
          await axios.put(`${this.apiUrl}/${this.modalData.id}`, data, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
        } else if (this.modalType === "delete") {
          await axios.delete(`${this.apiUrl}/${this.modalData.id}`, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
          });
        }

        this.modalOpen = false;
        await this.fetchQuestSteps();
      } catch (error) {
        console.error("Erreur lors de l’opération", error.response ? error.response.data : error.message);
      }
    },
    selectQuest(quest) {
      this.modalData.quest_id = quest.id;
      this.searchQuest = quest.title;
      this.showQuestList = false;
    },
    hideQuestList() {
      setTimeout(() => {
        this.showQuestList = false;
      }, 200);
    }
  }
};
</script>
