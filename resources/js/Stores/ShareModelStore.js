import { defineStore } from 'pinia'
import axios from 'axios'
import {useToastStore} from "@/Stores/ToastStore";
export const useShareModalStore = defineStore('ShareModalStore', {
    state: () => {
        return {
            links: [],
            showMenu: false,
        }
    },
    actions: {
        async getShareLinks(link, title) {
            try {
                const response = await axios.get(route("api.share.index"), { params: { link, title } });
                this.links = response.data.links;
            } catch (error) {
                console.log(error);
            }
        },

    }
})
