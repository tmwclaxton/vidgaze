import { defineStore } from 'pinia'
import axios from 'axios'
import {useToastStore} from "@/Stores/ToastStore";
import {usePage} from "@inertiajs/vue3";
export const usePlaylistModalStore = defineStore('PlaylistModalStore', {
    state: () => {
        return {
            videoIds: [],
            playlists: [],
            showMenu: false,
        }
    },
    actions: {
        async getPlaylists() {
            if (usePage().props.auth.user !== null) {
                axios.get(route('playlists.modal.refresh', {video_ids:  this.videoIds.join()}))
                    .then(response => {
                        this.playlists = response.data['playlists'];
                        // this.showMenu = true;
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }

        },
        async addVideosToPlaylist(playlistId) {
            if (usePage().props.auth.user !== null) {
                let toastStore = useToastStore();
                axios.post('/playlists/' + playlistId + '/videos', { video_ids: this.videoIds.join() })
                    .then(response => {
                        this.getPlaylists();
                        toastStore.add({
                            message:"Added to playlist",
                            type: 'success',
                        });
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
        async removeVideosFromPlaylist(playlistId) {

            if (usePage().props.auth.user !== null) {
                let toastStore = useToastStore();
                axios.delete('/playlists/' + playlistId + '/videos', { data: { video_ids: this.videoIds.join() } })
                    .then(response => {
                        this.getPlaylists();
                        toastStore.add({
                            message:"Removed from playlist",
                            type: 'warning',
                        });
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
        async createPlaylist(name, visibility) {

            if (usePage().props.auth.user !== null) {
                let toastStore = useToastStore();
                axios.post('/playlist/create', { name: name, visibility: visibility })
                    .then(response => {
                        this.getPlaylists();
                        toastStore.add({
                            message:"Playlist created",
                            type: 'success',
                        });
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },


    }
})
