import { defineStore } from 'pinia'
import axios from 'axios'
import {useToastStore} from "@/Stores/ToastStore";
import {usePage} from "@inertiajs/vue3";
import {useAuthStore} from "@/Stores/AuthStore";
export const usePinModalStore = defineStore('PinModalStore', {
    state: () => {
        return {
            video_id: null,
            category: null,
            duration: 0,
            categories: [],
            showMenu: false,
        }
    },
    actions: {
        async getCategories(channel_id, page = 1, perPage = 20) {
            let playlists;
            await axios.get(route('api.playlist.channelIndex'), {
                params: {
                    channel_id: channel_id,
                    page: page,
                    per_page: perPage
                }
            }).then(response => {
                playlists = response.data.playlists.data;
            }).
            catch(error => {
                // console.log(error);
                useToastStore().add({
                    message: error.response.data.message,
                    type: 'warning',
                });
            });

            return {
                'playlists': playlists,
            };
        },

        async pinVideo() {
            await axios.post(route('api.moderator.pin_video'), {
                video_id: this.video_id,
                duration: this.duration,
            }).then(response => {
                useToastStore().add({
                    message: response.data.message,
                    type: 'success',
                });
                this.showMenu = false;
                this.reset();
            }).catch(error => {
                useToastStore().add({
                    message: error.response.data.message,
                    type: 'warning',
                });
            });
        },

        async unpinVideo() {
            await axios.post(route('api.moderator.unpin_video'), {
                video_id: this.video_id,
            }).then(response => {
                useToastStore().add({
                    message: response.data.message,
                    type: 'success',
                });
                this.showMenu = false;
                this.reset();
            }).catch(error => {
                useToastStore().add({
                    message: error.response.data.message,
                    type: 'warning',
                });
            });
        },

        reset() {
            this.video_id = null;
            this.category = null;
            this.duration = 0;
            this.categories = [];
            this.showMenu = false;
        }



    }
})
