<script>
import AdminLayout from '../../layouts/AdminLayout.vue';
import axios from 'axios';
import { Bar, Pie, Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
  PointElement,
  LineElement
} from 'chart.js';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
    PointElement,
    LineElement
);

export default {
  components: { AdminLayout, Bar, Pie, Line },
  data() {
    return {
      stats: {
        totalDofus: 0,
        totalQuests: 0,
        totalSteps: 0,
        totalUsers: 0,
      },
      questsByDofus: [],
      questsOverTime: [],
      recentQuests: [],
      recentSteps: [],
      apiUrl: 'http://127.0.0.1:8000/api',
    };
  },
  async mounted() {
    await this.fetchStats();
    await this.fetchQuestsByDofus();
    await this.fetchQuestsOverTime();
    await this.fetchRecentData();
  },
  methods: {
    async fetchStats() {
      try {
        const response = await axios.get(`${this.apiUrl}/dashboard/stats`, {
          headers: {Authorization: `Bearer ${localStorage.getItem('token')}`},
        });
        this.stats = response.data;
      } catch (error) {
        console.error("Erreur chargement des statistiques", error);
      }
    },
    async fetchQuestsByDofus() {
      try {
        const response = await axios.get(`${this.apiUrl}/dashboard/quests-by-dofus`, {
          headers: {Authorization: `Bearer ${localStorage.getItem('token')}`},
        });
        this.questsByDofus = response.data;
      } catch (error) {
        console.error("Erreur chargement des quêtes par Dofus", error);
      }
    },
    async fetchQuestsOverTime() {
      try {
        const response = await axios.get(`${this.apiUrl}/dashboard/quests-over-time`, {
          headers: {Authorization: `Bearer ${localStorage.getItem('token')}`},
        });
        this.questsOverTime = response.data;
      } catch (error) {
        console.error("Erreur chargement des quêtes dans le temps", error);
      }
    },
    async fetchRecentData() {
      try {
        const response = await axios.get(`${this.apiUrl}/dashboard/recent-data`, {
          headers: {Authorization: `Bearer ${localStorage.getItem('token')}`},
        });
        this.recentQuests = response.data.quests;
        this.recentSteps = response.data.steps;
      } catch (error) {
        console.error("Erreur chargement des données récentes", error);
      }
    },
  },
};
</script>

<template>
  <AdminLayout>
    <div class="p-6">
      <h1 class="text-3xl font-bold mb-6 text-center">Dashboard</h1>

      <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-500 text-white p-4 rounded-lg text-center">
          <p class="text-2xl font-bold">{{ stats.totalDofus }}</p>
          <p class="text-lg">Dofus</p>
        </div>
        <div class="bg-green-500 text-white p-4 rounded-lg text-center">
          <p class="text-2xl font-bold">{{ stats.totalQuests }}</p>
          <p class="text-lg">Quêtes</p>
        </div>
        <div class="bg-yellow-500 text-white p-4 rounded-lg text-center">
          <p class="text-2xl font-bold">{{ stats.totalSteps }}</p>
          <p class="text-lg">Étapes</p>
        </div>
        <div class="bg-purple-500 text-white p-4 rounded-lg text-center">
          <p class="text-2xl font-bold">{{ stats.totalUsers }}</p>
          <p class="text-lg">Utilisateurs</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 center">
        <div class="bg-white p-4 rounded-lg shadow">
          <h2 class="text-xl font-semibold mb-4">Répartition des Quêtes par Dofus</h2>
          <Pie v-if="questsByDofus.length"
               :data="{ labels: questsByDofus.map(d => d.name), datasets: [{ data: questsByDofus.map(d => d.count), backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56'] }] }"/>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
