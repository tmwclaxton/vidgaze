import { defineStore } from 'pinia'
import axios from 'axios'
import {usePage} from "@inertiajs/vue3";
import {useToastStore} from "@/Stores/ToastStore";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";

export const useContentModalStore = defineStore('ContentModalStore', {

    state: () => {
        return {
            item: null,
            itemType: null,
            showMenu: false,
            inWatchLater: false,
            videoDisinterest: false,
            channelDisinterest: false,
            reportedContent: false,
            x: 0,
            y: 0,
            widthOfMenu: usePage().props.auth.user !== null ? 250 : 120,
            heightOfMenu: 0,
        }
    },
    actions: {
        setMenuShow(value) {

            if (this.item !== null) {
            // if logged in, get video details like is the video in watch later playlist etc
            if (usePage().props.auth.user !== null && value === true) {
                if (this.itemType === 'video') {
                    this.getVideoDetails().then(r => {

                    });
                }
            }
            // menu can't hidden or else can't find it to get width and height
            // update doesn't seem to work
            const menuRef = document.getElementById("menu");
            if (menuRef !== null) {
                this.widthOfMenu = menuRef.offsetWidth;
                this.heightOfMenu =  menuRef.offsetHeight;
            }
            // position menu so it doesn't go off screen
            this.setCoordinates();

            this.showMenu = value;
            }
        },
        // position menu so it doesn't go off screen
        setCoordinates() {
            if (this.item !== null && this.itemType !== null) {
                const buttonRect = document.getElementById('dotsButton_' + this.itemType + '_' + this.item.id).getBoundingClientRect();
                this.y = buttonRect.top + window.scrollY + 37;
                this.x = buttonRect.left + window.scrollX ;

                // Adjust x if menu goes off right edge of screen
                if (this.x + this.widthOfMenu > window.innerWidth) {
                    this.x -= this.widthOfMenu;
                }
            }
        },
        async getVideoDetails() {
            if (this.item !== null) {
                const videoId = this.item.id;
                try {
                    const response = await axios.get(route('videos.details', { videoId: videoId }));
                    const data = response.data;
                    this.inWatchLater = data.inWatchLater;
                    this.videoDisinterest = data.videoDisinterest;
                    this.channelDisinterest = data.channelDisinterest;
                    this.reportedContent = data.reportedContent;
                    // this sets the watch later button to the correct state
                    if (this.inWatchLater) {
                        document.getElementById('toggleInWatchLater_' + this.item.id).click();
                    } else  {
                        document.getElementById('toggleNotInWatchLater_' + this.item.id).click();
                    }
                    this.showMenu = true;
                } catch (error) {
                    console.log(error);
                }
            }
        },

        async toggleVideoDisinterest(video_id, toggle) {
            const url = '/videos/' + video_id + '/disinterest' ;
            const method = toggle ? 'delete' : 'post';
            let toastStore = useToastStore();

            axios[method](url)
                .then(response => {
                    const message = toggle ? 'Got it, we will show you more videos like this' : 'Got it, we will show you less videos like this' ;
                    const type = toggle ? 'success' : 'error';
                    this.videoDisinterest = !this.videoDisinterest;
                    this.showMenu = false;
                    // show toast
                    toastStore.add({
                        message,
                        type,
                    });
                    // this will hide the video and show the hidden content cover
                    if (!toggle) {
                        document.getElementById('hide_' + this.itemType + '_' + this.item.id).click();
                    }
                })
                .catch(error => {
                    if (error.response.data.error !== undefined) {
                        toastStore.add({
                            message: error.response.data.error,
                            type: 'error',
                        });
                    } else {
                        toastStore.add({
                            message: "Something went wrong.",
                            type: 'error',
                        });
                    }
                    return false;
                });
            return true;
        },

        async reportContent(id) {
            let url = '';
            if ( this.itemType === 'video' || this.itemType === 'short') {
                url = route('video.report.add', {id: id}); ///videos/{id}/report
            } else if ( this.itemType === 'stream') {
                url = route('stream.report.add', {id: id});
            }
            // this will hide the video and show the hidden content cover
            document.getElementById('hide_' + this.itemType + '_' + this.item.id).click();
            const method = 'post';
            let toastStore = useToastStore();

            axios[method](url)
                .then(response => {
                    this.reportedContent = true;
                    this.showMenu = false;
                    toastStore.add({
                        message: 'Thank you for reporting this ' + this.itemType + '. We will review it as soon as possible.',
                        type: 'success',
                    });
                })
                .catch(error => {
                    toastStore.add({
                        message: "Sorry, we couldn't report this " + this.itemType + ". Please try again later.",
                        type: 'error',
                    });
                    return false;
                });
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
                    this.showMenu = false;
                    // this will hide the video
                    if (!toggle) {
                        document.getElementById('hide_' + this.itemType + '_' + this.item.id).click();
                    }
                })
                .catch(error => {
                    if (error.response.data.error === undefined) {
                        toastStore.add({
                            message: error.response.data.error,
                            type: 'error',
                        });
                    } else {
                        toastStore.add({
                            message: "Something went wrong.",
                            type: 'error',
                        });
                    }
                    return false;
                });
            return true;
        },
        async toggleWatchLater(id, toggle) {
            const url = '/playlists/watch_later/videos';
            let playlistModalStore = usePlaylistModalStore();
            // set the video id in the playlist modal store
            playlistModalStore.videoIds = [id];
            // add or remove the video from the watch later playlist
            if (toggle) {
                await playlistModalStore.removeVideosFromPlaylist("watch_later");
            } else {
                await playlistModalStore.addVideosToPlaylist("watch_later");
            }
            // not really needed because we reload the modal each time it opens
            this.inWatchLater = !this.inWatchLater;
            if (this.inWatchLater) {
                document.getElementById('toggleInWatchLater_' + this.item.id).click();
            } else  {
                document.getElementById('toggleNotInWatchLater_' + this.item.id).click();
            }
            return true;
        },


    }
})
