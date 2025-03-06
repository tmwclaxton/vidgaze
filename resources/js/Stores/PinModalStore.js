import {defineStore} from 'pinia'
import axios from 'axios'
import {useToastStore} from "@/Stores/ToastStore";
import {usePage} from "@inertiajs/vue3";
import {useAuthStore} from "@/Stores/AuthStore";
export const usePinModalStore = defineStore('PinModalStore', {
    state: () => {
        return {
            'video_id': null,
            'category': null,
            'duration': 172800,
            'categories': [],
            'pinDetails': [],
            'showMenu': false,
            'selectedCategory': null,
        }
    },
    actions: {
        async getVideoCategories() {
            await axios.get(route('api.category.index.videos'),{}
            ).then(response => {
                    this.categories = response.data['categories'];
                })
                .catch(error => {
                    console.log(error);
                });
        },

        async getPinDetails() {
            await axios.post(route('api.moderator.get_pin_status'), {
                video_id: this.video_id,
            }).then(response => {
                this.pinDetails = response.data;
            }).catch(error => {
                useToastStore().add({
                    message: error.response.data.message,
                    type: 'warning',
                });
            });
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
            this.showMenu = false;
        }



    }
})
