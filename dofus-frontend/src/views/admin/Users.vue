<template>
  <AdminLayout>
    <div class="p-6">
      <h1 class="text-3xl font-bold mb-4">Gestion des Utilisateurs</h1>

      <!-- Tableau des utilisateurs -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
          <thead class="bg-gray-800 text-white">
          <tr>
            <th class="py-3 px-6 text-left">ID</th>
            <th class="py-3 px-6 text-left">Nom d'utilisateur</th>
            <th class="py-3 px-6 text-left">Rôle</th>
            <th class="py-3 px-6 text-left">Actions</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="user in users" :key="user.id" class="border-b">
            <td class="py-3 px-6">{{ user.id }}</td>
            <td class="py-3 px-6">{{ user.username }}</td>
            <td class="py-3 px-6">{{ user.roles.join(', ') }}</td>
            <td class="py-3 px-6">
              <button @click="deleteUser(user.id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-700">
                Supprimer
              </button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

<script>
import AdminLayout from '../../layouts/AdminLayout.vue';
import axios from 'axios';

export default {
  components: { AdminLayout },
  data() {
    return {
      users: []
    };
  },
  async mounted() {
    await this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/users', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        this.users = response.data;
      } catch (error) {
        console.error("Erreur lors de la récupération des utilisateurs", error);
      }
    },
    async deleteUser(userId) {
      if (!confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) return;

      try {
        await axios.delete(`http://127.0.0.1:8000/api/users/${userId}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        this.users = this.users.filter(user => user.id !== userId);
      } catch (error) {
        console.error("Erreur lors de la suppression", error);
      }
    }
  }
};
</script>
