import { defineStore } from 'pinia'
import {useToastStore} from "@/Stores/ToastStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import {usePage} from "@inertiajs/vue3";

export const useContentRoutesStore = defineStore('ContentRoutesStore', {
    state: () => {
        return {


        }
    },
    getters: {

    },
    actions: {
        // get videos
        async getVideos(category = "popular", perPage = 20, videoIds = [], shorts = false) {
            const response = await axios.get(route('videos.infinite'), {
                params: {
                    category: category,
                    perPage: perPage,
                    videoIds
                }
            }).catch(error => {
                    console.log(error);
                }
            )
            return response;
        },

        // get top streams
        async getTopStreams() {
            const response = await axios.get(route('streams.top'))
                .catch(error => {
                    console.log(error);
                })
            return response;

        },

        // get categories
        async getCategories(perPage = 10) {
            const response = await axios.get(route('categories.index'),  {
                params: {
                    perPage: perPage
                }})
                .catch(error => {
                    console.log(error);
                })
            return response;
        },

        // get categories with streams
        async getCategoriesWithStreams(perPage = 10, categoryIds ) {
            const response = axios.get(route('categories.infinite'),  {
                params: {
                    perPage: 8,
                    categoryIds
                } } )
                .catch(error => {
                    console.log(error);
                });
            return response;
        },


    },
});
