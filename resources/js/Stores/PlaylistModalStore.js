import { defineStore } from 'pinia'
import axios from 'axios'
export const usePlaylistModalStore = defineStore('PlaylistModalStore', {
    state: () => {
        return {
            videoIds: [],
            playlists: [],
            showMenu: false,
        }
    },
    actions: {


    }
})
