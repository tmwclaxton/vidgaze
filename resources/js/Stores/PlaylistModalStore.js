import { defineStore } from 'pinia'
import axios from 'axios'
import {useToastStore} from "@/Stores/ToastStore";
export const usePlaylistModalStore = defineStore('PlaylistModalStore', {
    state: () => {
        return {
            videoIds: [53],
            playlists: [],
            showMenu: false,
        }
    },
    actions: {
        async getPlaylists() {
            axios.get(route('playlists.modal.refresh', {video_ids:  this.videoIds.join()}))
                .then(response => {
                    this.playlists = response.data['playlists'];
                })
                .catch(error => {
                    console.log(error);
                });
        },
        async addVideosToPlaylist(playlistId) {
            let toastStore = useToastStore();
            axios.post('/playlists/' + playlistId + '/videos', { video_ids: this.videoIds.join() })
                .then(response => {
                    this.getPlaylists();
                    toastStore.add({
                        message:"Added to playlist",
                    });
                })
                .catch(error => {
                    console.log(error);
                });
        },
        async removeVideosFromPlaylist(playlistId) {
            let toastStore = useToastStore();
            axios.delete('/playlists/' + playlistId + '/videos', { data: { video_ids: this.videoIds.join() } })
                .then(response => {
                    this.getPlaylists();
                    toastStore.add({
                        message:"Removed from playlist",
                        type: 'error',
                    });
                })
                .catch(error => {
                    console.log(error);
                });
        }


    }
})
