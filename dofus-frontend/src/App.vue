<template>
  <Navbar v-if="!isAdminRoute && !isLoginRoute && !isRegisterRoute"/>
  <ProfileModal />
  <router-view />
</template>

<script>
import { computed } from "vue";
import { useRoute } from "vue-router";
import Navbar from "@/components/NavBar.vue";
import ProfileModal from "@/components/ProfileModal.vue";
export default {
  components: {
    Navbar,
    ProfileModal,
  },
  setup() {
    const route = useRoute();

    // Vérifie si l'on est sur une route d'administration
    const isAdminRoute = computed(() => route.path.startsWith("/admin"));

    // Vérifie si l'on est sur la page de connexion
    const isLoginRoute = computed(() => route.path === "/login");

    const isRegisterRoute = computed(() => route.path === "/register");

    return { isAdminRoute, isLoginRoute, isRegisterRoute };
  }
};
</script>
