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
            createPage: false,
        }
    },
    actions: {
        async getPlaylists(where = 'modal') {
            if (useAuthStore().user !== null) {
                axios.get(route('api.playlist.index', {
                    where: where,
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

        async getPlaylist(playlist_slug, page = 1, perPage = 20) {
            let playlist;
            let videos;
            if (useAuthStore().user !== null) {
                await axios.get(route('api.playlist.videos.index'), {
                    params: {
                        playlist_slug: playlist_slug,
                        page: page,
                        per_page: perPage
                    }
                }).then(response => {

                    // console.log(response);
                    playlist = response.data.playlist;
                    videos = response.data.videos.data;
                }).
                catch(error => {
                    console.log(error);
                });

                return [playlist, videos];
            }
        },


        async addVideosToPlaylist(playlist_slug) {
            if (useAuthStore().user !== null) {
                let toastStore = useToastStore();
                axios.post(route('api.playlist.video.create', {
                    playlist_slug: playlist_slug,
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
        async removeVideosFromPlaylist(playlist_slug) {
            if (useAuthStore().user !== null) {
                let toastStore = useToastStore();
                axios.delete(route('api.playlist.video.destroy', {
                    playlist_slug: playlist_slug,
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
                        // console.log(error);
                        useToastStore().add({
                            message: error.response.data.message,
                            type: 'warning',
                        });
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
                        if (this.videoIds.length > 0) {
                            this.getPlaylists();
                        } else {
                            this.getPlaylists('all')
                        }
                        toastStore.add({
                            message:"Playlist created",
                            type: 'success',
                        });
                    })
                    .catch(error => {
                        // console.log(error);
                        useToastStore().add({
                            message: error.response.data.message,
                            type: 'warning',
                        });
                    });
            }
        },

        async deletePlaylist(playlist_id) {
            if (useAuthStore().user !== null) {
                let toastStore = useToastStore();
                axios.delete(route('api.playlist.destroy', {
                    playlist_id: playlist_id,
                }))
                    .then(response => {
                        this.getPlaylists();
                        toastStore.add({
                            message:"Playlist deleted",
                            type: 'success',
                        });
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },

        async updatePlaylist(playlist_id, name, visibility) {
            if (useAuthStore().user !== null) {
                let toastStore = useToastStore();
                axios.patch(route('api.playlist.update', {
                    playlist_id: playlist_id,
                    name: name,
                    visibility: visibility
                }))
                    .then(response => {
                        this.getPlaylists();
                        toastStore.add({
                            message:"Playlist updated",
                            type: 'success',
                        });
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        }


    }
})
