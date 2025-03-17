<template>
  <div class="flex min-h-screen items-center justify-center bg-gray-900">
    <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md">
      <h2 class="text-2xl font-semibold text-white text-center mb-6">Créer un compte</h2>

      <form @submit.prevent="register">
        <!-- Pseudo -->
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium">Pseudo</label>
          <input v-model="form.pseudo" type="text" class="w-full bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500">
        </div>

        <!-- username -->
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium">Username</label>
          <input v-model="form.username" type="text" class="w-full bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500">
        </div>

        <!-- Mot de passe -->
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium">Mot de passe</label>
          <input v-model="form.password" type="password" class="w-full bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500">
        </div>

        <!-- Confirmation du mot de passe -->
        <div class="mb-4">
          <label class="block text-gray-300 text-sm font-medium">Confirmer le mot de passe</label>
          <input v-model="form.confirmPassword" type="password" class="w-full bg-gray-700 text-white border border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500">
        </div>

        <!-- Message d'erreur -->
        <p v-if="errorMessage" class="text-red-500 text-sm mb-4">{{ errorMessage }}</p>

        <!-- Bouton S'inscrire -->
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded">
          S'inscrire
        </button>
      </form>

      <p class="text-gray-400 text-sm text-center mt-4">
        Déjà un compte ?
        <router-link to="/login" class="text-blue-400 hover:underline">Connectez-vous</router-link>
      </p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      form: {
        pseudo: "",
        password: "",
        confirmPassword: "",
      },
      errorMessage: "",
    };
  },
  methods: {
    async register() {
      this.errorMessage = "";

      if (this.form.password !== this.form.confirmPassword) {
        this.errorMessage = "Les mots de passe ne correspondent pas.";
        return;
      }

      try {
        await axios.post("http://127.0.0.1:8000/api/register", {
          pseudo: this.form.pseudo,
          username: this.form.username,
          password: this.form.password,
        });

        // Rediriger vers la connexion après inscription
        this.$router.push("/login");
      } catch (error) {
        this.errorMessage = error.response?.data?.message || "Erreur lors de l'inscription.";
        console.error("Erreur d'inscription :", error.response?.data || error);
      }
    },
  },
};
</script>

<style scoped>
/* Ajoute un effet de focus aux champs */
input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 5px rgba(59, 130, 246, 0.5);
}
</style>
