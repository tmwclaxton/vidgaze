import { defineStore } from 'pinia'
import axios from 'axios'
import {useToastStore} from "@/Stores/ToastStore";
export const useConfirmModalStore = defineStore('ConfirmModalStore', {
    state: () => {
        return {
            show: false,
            title: '',
            buttonOneText: '',
            buttonTwoText: '',
            continue: null,
        }
    },
    actions: {
        clickButtonOne() {
            this.reset();


        },
        clickButtonTwo() {
            this.continue();
            this.reset();
        },

        reset() {
            this.show = false
            this.title = '';
            this.buttonOneText = '';
            this.buttonTwoText = '';
            this.continue = null;
        }

    }
})
