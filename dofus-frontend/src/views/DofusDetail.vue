<template>
  <div class="relative bg-gray-900 min-h-screen text-white">
    <!-- Conteneur de l'image de fond avec overlay gradient amélioré -->
    <div class="relative h-[70vh] overflow-hidden">
      <!-- Dégradé optimisé pour correspondre à la maquette -->
      <div class="absolute inset-0 bg-gradient-to-b from-transparent via-gray-900/50 to-gray-900 z-10"></div>
      <img :src="dofus.image" alt="Background" class="w-full h-full object-cover">
    </div>

    <!-- Contenu header centré avec l'icône et le titre -->
    <div class="absolute top-0 inset-x-0 h-[70vh] flex flex-col items-center justify-center text-white text-center z-20">
      <img :src="dofus.icon" alt="Icone" class="w-24 h-24 mb-6 drop-shadow-lg floating-icon">
      <h1 class="text-8xl font-bold uppercase tracking-wider drop-shadow-md">{{ dofus.name }}</h1>

      <!-- Indicateurs avec icônes (quêtes, donjons, succès) -->
      <div class="flex space-x-16 mt-8 text-xl items-center">
        <div class="flex items-center">
          <img src="https://dofusplanet.fr/img/icons/quest_book2.png" class="w-8 h-8 mr-3">
          {{ dofus.quest_count }} quêtes
        </div>
        <div class="flex items-center">
          <img src="https://dofusplanet.fr/img/icons/dungeon.png" class="w-8 h-8 mr-3">
          {{ dofus.dungeon_count }} donjons
        </div>
        <div class="flex items-center">
          <img src="https://dofusplanet.fr/img/icons/achievement.png" class="w-8 h-8 mr-3">
          {{ dofus.achievement_count }} succès
        </div>
      </div>

      <!-- Séparateur décoratif -->
      <div class="flex items-center space-x-4 mt-12">
        <div class="w-8 h-1 bg-yellow-500 rounded-full"></div>
        <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
        <div class="w-8 h-1 bg-yellow-500 rounded-full"></div>
      </div>
    </div>

    <!-- Section contenu (quêtes & récompenses) -->
    <div class="content-section relative z-10 p-8 flex gap-6 mt-8">
      <div class="w-3/4">
        <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6 mb-8">
          <h2 class="text-3xl font-bold mb-6">Quêtes principales</h2>

          <div v-for="quest in quests" :key="quest.id"
               class="bg-gray-700/90 rounded-lg p-4 mb-4 transition hover:bg-gray-700"
               :class="{ 'text-green-400 line-through opacity-50': validatedQuests.includes(quest.id) }">
            <button @click="toggleQuest(quest.id)" class="flex justify-between w-full text-lg font-semibold">
              {{ quest.title }} ({{ quest.steps.length }} étapes)
              <span>{{ activeQuests.includes(quest.id) ? '-' : '+' }}</span>
            </button>

            <div v-if="activeQuests.includes(quest.id)" class="mt-4">
              <ul>
                <li v-for="step in quest.steps" :key="step.id"
                    class="bg-gray-800 p-3 mt-2 rounded flex flex-col justify-between cursor-pointer transition"
                    :class="{ 'text-green-400 line-through opacity-50': validatedSteps.includes(step.id) }"
                    @click="toggleStepValidation(step.id)">
                  <div>
                    <strong>{{ step.title }}</strong> - {{ step.description }}
                    <strong class="relative cursor-pointer text-blue-400 hover:underline"
                            @click.stop="copyCoordinates(step.positionX, step.positionY, step.id)">
                      ({{ step.positionX }},{{ step.positionY }})
                      <transition name="fade">
                        <span v-if="copiedStepId === step.id"
                              class="absolute top-[-30px] left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-3 py-1 rounded shadow-md z-50 whitespace-nowrap">
                          Copié !
                        </span>
                      </transition>
                    </strong>
                    <span v-if="validatedSteps.includes(step.id)" class="text-green-400">✔</span>
                  </div>
                  <div v-if="step.image" class="mt-2 flex flex-wrap gap-3">
                    <img v-for="(img, index) in step.image.split(';')" :key="index"
                         :src="img.trim()" alt="Illustration"
                         class="w-40 h-40 object-cover rounded-lg border border-gray-600 shadow cursor-pointer"
                         @click.stop="openImageModal(img.trim())">
                  </div>
                </li>
                <div class="flex justify-end mt-4">
                  <button @click="toggleQuestValidation(quest.id)"
                          class="px-4 py-2 rounded font-semibold transition"
                          :class="validatedQuests.includes(quest.id) ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-green-600 hover:bg-green-700 text-white'">
                    {{ validatedQuests.includes(quest.id) ? 'Dévalider la quête' : 'Valider toute la quête' }}
                  </button>
                </div>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Colonne des ressources nécessaires -->
      <div class="w-1/4">
        <div class="bg-gray-800/80 backdrop-blur rounded-lg p-6">
          <h2 class="text-2xl font-bold mb-4">Récompenses :</h2>
          <div v-for="reward in rewards" :key="reward.name" class="flex items-center mb-3">
            <div class="mr-3 text-xl">{{ reward.quantity }} x</div>
            <span>{{ reward.name }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modale d'affichage de l'image agrandie -->
    <div v-if="modalImage" class="fixed inset-0 backdrop-blur-md bg-opacity-75 flex items-center justify-center z-50">
      <div class="relative">
        <img :src="modalImage" alt="Image agrandie" class="max-w-4xl max-h-screen rounded-lg shadow-lg">
        <button @click="modalImage = null" class="absolute top-2 right-2 bg-gray-800 text-white px-3 py-1 rounded">
          ✖
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue';
import axios from 'axios';

export default {
  components: { Navbar },
  data() {
    return {
      dofus: {},
      quests: [],
      activeQuests: [],
      rewards: [],
      validatedSteps: [],
      validatedQuests: [],
      copiedStepId: null,
      apiUrl: `http://127.0.0.1:8000/api/dofus/${this.$route.params.id}`,
      questApiUrl: `http://127.0.0.1:8000/api/dofus/${this.$route.params.id}/quests`,
      modalImage: null,
    };
  },
  async mounted() {
    await this.fetchDofusDetails();
    await this.fetchDofusQuests();
    await this.fetchValidatedSteps();
  },
  methods: {
    openImageModal(image) {
      this.modalImage = image;
    },
    async copyCoordinates(x, y, stepId) {
      const command = `/travel ${x} ${y}`;
      try {
        await navigator.clipboard.writeText(command);
        this.copiedStepId = stepId;
        setTimeout(() => {
          this.copiedStepId = null;
        }, 1500);
      } catch (err) {
        console.error("Erreur lors de la copie", err);
      }
    },
    async fetchDofusDetails() {
      try {
        const response = await axios.get(this.apiUrl, {
          headers: {Authorization: `Bearer ${localStorage.getItem('token')}`},
        });
        this.dofus = response.data;
      } catch (error) {
        console.error("Erreur chargement du Dofus", error);
      }
    },
    async fetchDofusQuests() {
      try {
        const response = await axios.get(this.questApiUrl, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });

        this.quests = response.data.map(quest => ({
          ...quest,
          rewards: quest.rewards || []
        }));

        const rewardsMap = {};
        this.quests.forEach(quest => {
          quest.rewards.forEach(reward => {
            if (rewardsMap[reward.name]) {
              rewardsMap[reward.name] += reward.quantity;
            } else {
              rewardsMap[reward.name] = reward.quantity;
            }
          });
        });

        this.rewards = Object.entries(rewardsMap).map(([name, quantity]) => ({
          name,
          quantity
        }));

      } catch (error) {
        console.error("Erreur chargement des quêtes", error);
      }
    },
    async fetchValidatedSteps() {
      try {
        const response = await axios.get("http://127.0.0.1:8000/api/user/quest-step", {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });

        this.validatedSteps = response.data.map(step => step.id);

        const questResponse = await axios.get("http://127.0.0.1:8000/api/user/quest", {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        });

        if (questResponse.data && Array.isArray(questResponse.data)) {
          this.validatedQuests = questResponse.data.map(quest => quest.id);
        } else {
          this.validatedQuests = [];
        }

      } catch (error) {
        console.error("Erreur récupération étapes ou quêtes validées", error.response ? error.response.data : error.message);
      }
    },
    async toggleStepValidation(stepId) {
      try {
        await axios.post(
            "http://127.0.0.1:8000/api/user/quest-step/validate",
            { step_id: stepId },
            { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } }
        );

        if (this.validatedSteps.includes(stepId)) {
          this.validatedSteps = this.validatedSteps.filter(id => id !== stepId);
        } else {
          this.validatedSteps.push(stepId);
        }

        await this.fetchValidatedSteps();
      } catch (error) {
        console.error("Erreur validation étape", error);
      }
    },
    toggleQuest(questId) {
      const index = this.activeQuests.indexOf(questId);
      if (index > -1) {
        this.activeQuests.splice(index, 1);
      } else {
        this.activeQuests.push(questId);
      }
    },
    async toggleQuestValidation(questId) {
      try {
        const url = this.validatedQuests.includes(questId)
            ? "http://127.0.0.1:8000/api/user/quest/unvalidate"
            : "http://127.0.0.1:8000/api/user/quest/validate";

        await axios({
          method: this.validatedQuests.includes(questId) ? "DELETE" : "POST",
          url: url,
          data: { quest_id: questId },
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });

        if (this.validatedQuests.includes(questId)) {
          this.validatedQuests = this.validatedQuests.filter(id => id !== questId);
        } else {
          this.validatedQuests.push(questId);
        }

      } catch (error) {
        console.error("Erreur validation/dévalidation quête", error);
      }
    },
  }
};
</script>

<style scoped>
/* Animation de flottement pour l'icône */
@keyframes float {
  0% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
  100% {
    transform: translateY(0px);
  }
}

.floating-icon {
  animation: float 3s ease-in-out infinite;
  filter: drop-shadow(0 0 10px rgba(255, 165, 0, 0.7));
}

/* Fade transition pour le message "Copié" */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

/* Styles généraux */
.text-green-400 {
  transition: all 0.3s ease-in-out;
}
.line-through {
  text-decoration: line-through;
}
.opacity-50 {
  opacity: 0.5;
}
</style>