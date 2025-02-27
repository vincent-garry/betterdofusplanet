import axios from 'axios';
import createPersistedState from 'vuex-persistedstate';

export default {
    namespaced: true,
    state: {
        token: localStorage.getItem('token') || null,
        user: null, // On s'assure que `user` est bien défini
    },
    mutations: {
        setToken(state, token) {
            state.token = token;
            localStorage.setItem('token', token);
        },
        setUser(state, user) {
            state.user = user;
        },
        logout(state) {
            state.token = null;
            state.user = null;
            localStorage.removeItem('token');
        }
    },
    actions: {
        async fetchUser({ commit }) {
            try {
                const response = await axios.get('http://127.0.0.1:8000/api/me', {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
                });
                commit('setUser', response.data);
            } catch (error) {
                commit('logout');
            }
        },
        logout({ commit }) {
            commit('logout');
        }
    },
    plugins: [createPersistedState()]
};
