import { defineStore } from 'pinia'
import axios from 'axios'
import {usePage} from "@inertiajs/vue3";
import {useToastStore} from "@/Stores/ToastStore";
// import {useToastStore} from "@/Stores/ToastStore";
// import {useToastStore} from "@/Stores/ToastStore.js";
export const useContentModalStore = defineStore('ContentModalStore', {

    state: () => {
        return {
            itemId: null,
            creatorId: null,
            itemType: null,
            showMenu: false,
            inWatchLater: false,
            videoDisinterest: false,
            channelDisinterest: false,
            reportVideo: false,
            x: 0,
            y: 0,
            widthOfMenu: 250,
            heightOfMenu: 0,
        }
    },
    actions: {
        setItemId(id) {
            this.itemId = id;
        },
        setMenuShow(value) {
            this.showMenu = value;
            // menu can't hidden or else can't find it to get width and height
            // update doesn't seem to work
            const menuRef = document.getElementById("menu");
            if (menuRef !== null) {
                this.widthOfMenu = menuRef.offsetWidth;
                this.heightOfMenu =  menuRef.offsetHeight;
            }
            // position menu so it doesn't go off screen
            this.setCoordinates();
            // if logged in, get video details like is the video in watch later playlist etc
            if (usePage().props.auth.user !== null && this.showMenu === true) {
                if (this.itemType === 'video') {
                    this.getVideoDetails().then(r => {
                    });
                }
            }
        },
        // position menu so it doesn't go off screen
        setCoordinates() {
            if (this.itemId !== null) {
                const buttonRect = document.getElementById('dotsButton_' + this.itemId).getBoundingClientRect();
                this.y = buttonRect.top + window.scrollY + 37;
                this.x = buttonRect.left + window.scrollX ;

                // Adjust x if menu goes off right edge of screen
                if (this.x + this.widthOfMenu > window.innerWidth) {
                    this.x -= this.widthOfMenu;
                }
            }
        },
        async getVideoDetails() {
            if (this.itemId !== null) {
                const videoId = this.itemId;
                try {
                    const response = await axios.get(route('videos.details', { videoId: videoId }));
                    const data = response.data;
                    this.inWatchLater = data.inWatchLater;
                    this.videoDisinterest = data.videoDisinterest;
                    this.channelDisinterest = data.channelDisinterest;
                    this.reportVideo = data.reportVideo;
                } catch (error) {
                    console.log(error);
                }
            }
        },

        async toggleChannelDisinterest(creator_id, toggle) {
            const url = '/channels/' + creator_id + '/disinterest' ;
            const method = toggle ? 'delete' : 'post';
            let toastStore = useToastStore();

            axios[method](url)
                .then(response => {
                    const message = toggle ? 'We will continue to show you this channel' : 'We will show you less of this channel' ;
                    const type = toggle ? 'success' : 'error';
                    toastStore.add({
                        message,
                        type,
                    });
                    this.channelDisinterest = !this.channelDisinterest;
                })
                .catch(error => {
                        toastStore.add({
                            message: error.response.data.error,
                            type: 'error',
                        });
                    return false;
                });
            return true;
        },

        async toggleWatchLater(id, toggle) {
            const url = '/playlists/watch_later/videos/' + id;
            const method = toggle ? 'delete' : 'post';
            let toastStore = useToastStore();

            axios[method](url)
                .then(response => {
                    const message = toggle ? 'Removed from Watch Later' : 'Added to Watch Later' ;
                    const type = toggle ? 'error' : 'success';
                    toastStore.add({
                        message,
                        type,
                    });
                    // not really needed because we reload the modal each time it opens
                    this.inWatchLater = !this.inWatchLater;
                })
                .catch(error => {
                    // console.log(error.response.data.error);
                    toastStore.add({
                        message: error.response.data.error,
                        type: 'error',
                    });
                    return false;
                });
            return true;

        }
    }
})
