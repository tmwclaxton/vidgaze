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
        async getVideo(slug) {
            const response = await axios.get(route('api.video.show', {slug: slug}))
            .catch((error) => {
                console.log(error);
            });
            return response.data.video;
        },

        async getStream(slug) {
            const response = await axios.get(route('api.stream.show', {slug: slug}))
            .catch((error) => {
                console.log(error);
            });
            return response.data.stream;
        },


        // get videos
        async getVideos(category = "popular", per_page = 20, video_ids = [], shorts = false, first_video_slug = null, creator_id = null) {
            // convert shorts to 1 or 0
            shorts = shorts ? 1 : 0;
            const response = await axios.get(route('api.video.index'), {
                params: {
                    category: category,
                    per_page: per_page,
                    video_ids,
                    shorts,
                    first_video_slug,
                    creator_id: creator_id
                }
            }).catch(error => {
                    console.log(error);
                }
            )
            if (response === undefined) {
                return [];
            }

            return response.data.videos.data;
        },

        // get channel videos
        async getChannelVideos(creator, per_page = 50, page = null) {
            let videos = [];
            let nextPage = null;
            const response = await axios.get(route('api.creator.videos', {
                slug: creator.slug,
                perPage: per_page,
                page: page
            }))
            .then((response) => {
                videos = response.data.videos.data;
                nextPage = response.data.next;
            })
            .catch((error) => {
                console.log(error);
            });
            return {
                'response': response,
                'videos': videos,
                'nextPage': nextPage
            } },

        // get top streams
        async getStreams(per_page = 10, category_id = null, skip = 0) {
            const response = await axios.get(route('api.stream.index'), {
                params: {
                    per_page: per_page,
                    category_id: category_id,
                    skip: skip,
                }
            }).catch(error => {
                console.log(error);
            })
            return response.data.streams.data;

        },

        // get categories
        async getCategories(per_page = 10, category_ids = null, ensure_details = false) {
            // change ensure_details to 1 or 0
            ensure_details = ensure_details ? 1 : 0;
            const response = await axios.get(route('api.category.index.streams'),  {
                params: {
                    per_page: per_page,
                    category_ids,
                    ensure_details: ensure_details,
                }})
                .catch(error => {
                    console.log(error);
                })
            return response.data.categories.data;
        },

        // get category
        async getCategory(slug) {
            const response = await axios.get(route('api.category.show', {slug: slug}))
                .catch(error => {
                    console.log(error);
                })
            return response.data.category;
        }



    },
});
