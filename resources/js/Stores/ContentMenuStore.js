import { defineStore } from 'pinia'
export const useContentMenuStore = defineStore('ContentMenuStore', {
    state: () => {
        return {
            itemId: null,
            showVideoMenu: false
        }
    },
    actions: {
        setItemId(id) {
            const state = this.$state
            state.itemId = id
        },
        getItemId() {
            const state = this.$state
            return state.itemId
        },
        setShowVideoMenu(value) {
            const state = this.$state
            state.showVideoMenu = value
        },
        getShowVideoMenu() {
            const state = this.$state
            return state.showVideoMenu
        }
    }
})
