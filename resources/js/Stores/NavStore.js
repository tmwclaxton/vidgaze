import { defineStore } from 'pinia'
export const useNavStore = defineStore('NavStore', {
    state: () => {
        return {
            showingStudioLinks: false,
            showingSearch: false,
            expandedSearch: false,

        }
    },
    actions: {

    }
})
