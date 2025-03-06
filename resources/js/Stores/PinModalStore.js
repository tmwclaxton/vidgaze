import {defineStore} from 'pinia'
import axios from 'axios'
import {useToastStore} from "@/Stores/ToastStore";
import {usePage} from "@inertiajs/vue3";
import {useAuthStore} from "@/Stores/AuthStore";
export const usePinModalStore = defineStore('PinModalStore', {
    state: () => {
        return {
            'video_id': null,
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
                // this.category = this.pinDetails.category_id;
                if (this.categories.data.length > 0) {
                    console.log(this.categories.data[0].id);
                    this.selectedCategory = this.categories.data.find(category => category.id === this.pinDetails.category_id);
                } else {
                    console.log('No categories');
                }
                this.duration = this.pinDetails.pin_duration ? this.pinDetails.pin_duration : 172800;
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

        async addCategoryToVideo() {

            await axios.post(route('api.moderator.add_category'), {
                video_id: this.video_id,
                category_id: this.selectedCategory.id,
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

        async removeCategoryFromVideo() {
            await axios.post(route('api.moderator.remove_category'), {
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
            this.category_id = null;
            this.duration = 0;
            this.showMenu = false;
        },

        async getPinnedVideos(per_page = 10, page = 1, category_slug = null, platform = null) {
            let pinnedVideos = [];
            await axios.get(route('api.video.pinned'), {
                params: {
                    per_page: per_page,
                    page: page,
                    category_slug: category_slug,
                    platform: platform,
                }
            }).then(response => {
                console.log(response.data);
                pinnedVideos = response.data.videos.data;
            }).catch(error => {
                console.log(error);
            });

            return pinnedVideos;

        }


    }
})
