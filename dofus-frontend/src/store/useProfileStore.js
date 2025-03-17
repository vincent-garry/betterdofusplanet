import { defineStore } from 'pinia';

export const useProfileStore = defineStore('profile', {
    state: () => ({
        isProfileOpen: false
    }),
    actions: {
        openProfile() {
            this.isProfileOpen = true;
        },
        closeProfile() {
            this.isProfileOpen = false;
        }
    }
});
