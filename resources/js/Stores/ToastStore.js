import { defineStore } from 'pinia'

export const useToastStore = defineStore('ToastStore', {
    state: () => {
        return { items: [] }
    },
    actions: {
        add(toast) {
            const state = this.$state
            state.items.push({
                key: Symbol(),
                ...toast
            })
        },
        remove(index) {
            const state = this.$state
            state.items.splice(index, 1)
        }
    }
})
