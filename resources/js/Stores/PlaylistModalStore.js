import { defineStore } from 'pinia'
import axios from 'axios'
import {useToastStore} from "@/Stores/ToastStore";
import {usePage} from "@inertiajs/vue3";
import {useAuthStore} from "@/Stores/AuthStore";
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
            if (useAuthStore().user !== null) {
                axios.get(route('api.playlist.index', {
                    video_ids:  this.videoIds.join()})
                )
                    .then(response => {
                        this.playlists = response.data['playlists'];
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },

        async getPlaylist(playlistId, page = 1, perPage = 20) {
            let playlist;
            let videos;
            if (useAuthStore().user !== null) {
                await axios.get(route('api.playlist.videos.index'), {
                    params: {
                        playlist_id: playlistId,
                        page: page,
                        per_page: perPage
                    }
                }).then(response => {

                    console.log(response);
                    playlist = response.data.playlist;
                    videos = response.data.videos.data;
                }).
                catch(error => {
                    console.log(error);
                });

                return [playlist, videos];
            }
        },


        async addVideosToPlaylist(playlistId) {
            if (useAuthStore().user !== null) {
                let toastStore = useToastStore();
                axios.post(route('api.playlist.video.create', {
                    playlist_id: playlistId,
                    video_ids: this.videoIds.join()
                }))
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
            if (useAuthStore().user !== null) {
                let toastStore = useToastStore();
                axios.delete(route('api.playlist.video.destroy', {
                    playlist_id: playlistId,
                    video_ids: this.videoIds.join()
                }))
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
            if (useAuthStore().user !== null) {
                let toastStore = useToastStore();
                axios.post(
                    route('api.playlist.create', {
                        name: name,
                        visibility: visibility
                    } ))
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
