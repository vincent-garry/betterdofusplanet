<template>
  <div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 h-screen text-white">
      <h2 class="text-xl font-bold">Admin Panel</h2>
      <nav class="mt-5">
        <router-link to="/admin" class="block py-2 hover:bg-gray-700 px-2 rounded">Dashboard</router-link>
        <router-link to="/admin/quests" class="block py-2 hover:bg-gray-700 px-2 rounded">Quêtes</router-link>
        <router-link to="/admin/dofus" class="block py-2 hover:bg-gray-700 px-2 rounded">Dofus</router-link>
        <router-link to="/admin/steps" class="block py-2 hover:bg-gray-700 px-2 rounded">Étapes de quête</router-link>
        <router-link to="/admin/users" class="block py-2 hover:bg-gray-700 px-2 rounded">Utilisateurs</router-link>
      </nav>
      <button @click="logout" class="mt-6 w-full bg-red-500 text-white p-2 rounded-lg hover:bg-red-600">
        Déconnexion
      </button>
    </aside>

    <!-- Contenu principal -->
    <main class="flex-1 bg-gray-100 p-6 overflow-hidden">
      <slot />
    </main>
  </div>
</template>

<script>
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
  setup() {
    const store = useStore();
    const router = useRouter();

    const logout = () => {
      store.dispatch('auth/logout');
      router.push('/login');
    };

    return { logout };
  }
};
</script>
