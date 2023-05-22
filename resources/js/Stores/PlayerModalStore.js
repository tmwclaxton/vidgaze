import { defineStore } from 'pinia'

export const usePlayerStore = defineStore('PlayerStore', {
    state: () => {
        return {
            object: {id: "M7lc1UVf-VE"},
            type: "YouTube",
            player: null,
            playing: false,
            autoplay: true,
            start_time: Math.floor(Math.random() * 1000),
        }
    },
    actions: {



    }
})
