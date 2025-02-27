import { createStore } from 'vuex';
import createPersistedState from 'vuex-persistedstate';
import auth from './auth'; // Import du module d'authentification

const store = createStore({
    modules: {
        auth
    },
    plugins: [createPersistedState()]
});

export default store;
