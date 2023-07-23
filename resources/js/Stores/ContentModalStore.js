import { defineStore } from 'pinia'
import axios from 'axios'
import {usePage} from "@inertiajs/vue3";
import {useToastStore} from "@/Stores/ToastStore";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import {useShareModalStore} from "@/Stores/ShareModelStore";

export const useContentModalStore = defineStore('ContentModalStore', {

    state: () => {
        return {
            item: null,
            itemType: null,
            showMenu: false,
            inWatchLater: false,
            itemDisinterest: false,
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

                if (this.itemType === 'video' || this.itemType === 'short') {
                    this.getVideoDetails().then(r => {
                        console.log(this.itemType);
                    });
                }
                if (this.itemType === 'stream') {
                    this.getStreamDetails().then(r => {
                        console.log(this.itemType);
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
            if (this.item !== null && this.itemType === 'video' || this.itemType === 'short') {
                const videoId = this.item.id;
                try {
                    const response = await axios.get(route('video.details', { videoId: videoId }));
                    const data = response.data;
                    this.inWatchLater = data.inWatchLater;
                    this.itemDisinterest = data.videoDisinterest;
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

        async getStreamDetails() {
            if (this.item !== null && this.itemType === 'stream') {
                const streamId = this.item.id;
                try {
                    const response = await axios.get(route('stream.details', { streamId: streamId }));
                    const data = response.data;
                    this.reportedContent = data.reportedContent;
                    this.itemDisinterest = data.streamDisinterest;
                    this.channelDisinterest = data.channelDisinterest;
                    this.showMenu = true;
                } catch (error) {
                    console.log(error);
                }
            }
        },
        async toggleItemDisinterest(item_id, toggle) {
            let url = ';'
            if (this.itemType === 'video' || this.itemType === 'short') {
                url = route('video.disinterest.toggle', {videoId: item_id});
            } else if (this.itemType === 'stream') {
                url = route('stream.disinterest.toggle', {streamId: item_id});
            }
            const method = 'post';
            let toastStore = useToastStore();

            axios[method](url)
                .then(response => {
                    this.itemDisinterest = !this.itemDisinterest;
                    this.showMenu = false;
                    // show toast
                    toastStore.add({
                        message: response.data.message,
                        type: response.data.type,
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
                            type: 'warning',
                        });
                    } else {
                        toastStore.add({
                            message: "Something went wrong.",
                            type: 'warning',
                        });
                    }
                    return false;
                });
            return true;
        },

        async reportContent(id) {
            let url = '';
            if ( this.itemType === 'video' || this.itemType === 'short') {
                url = route('video.report.toggle', {videoId: id}); ///videos/{id}/report
            } else if ( this.itemType === 'stream') {
                url = route('stream.report.toggle', {streamId: id});
            }
            // this will hide the video and show the hidden content cover
            if (!this.reportedContent) {
                document.getElementById('hide_' + this.itemType + '_' + this.item.id).click();
            }
            const method = 'post';
            let toastStore = useToastStore();

            axios[method](url)
                .then(response => {
                    this.reportedContent = true;
                    this.showMenu = false;
                    toastStore.add({
                        message: response.data.message,
                        type: response.data.type,
                    });
                })
                .catch(error => {
                    toastStore.add({
                        message: "Sorry, we couldn't report this " + this.itemType + ". Please try again later.",
                        type: 'warning',
                    });
                    return false;
                });
        },

        shareContent() {
            let shareStore = useShareModalStore();
            shareStore.showMenu = true; // show share menu
            this.showMenu = false; // hide content menu
            let link = '';
            let title = '';
            if (this.itemType === 'video') {
                link = route('watch.show', { video: {slug: this.item.slug } });
                title = "Check out this cool video on VidGaze" + this.item.title
                // console.log(link);
            } else if (this.itemType === 'stream') {
                link = route('stream.show', { stream: {slug: this.item.slug } });
                title = "Check out this cool stream on VidGaze" + this.item.title
            } else if (this.itemType === 'short') {
                link = route('short.show', { video: {slug: this.item.slug } });
                title = "Check out this cool short on VidGaze" + this.item.title
            }
            shareStore.getShareLinks(link, title);
        },

        async toggleChannelDisinterest(creator_id, toggle) {
            // const url = '/channels/' + creator_id + '/disinterest' ;
            let url = route('channel.disinterest.toggle', {channelId: creator_id});
            const method = 'post';
            let toastStore = useToastStore();

            axios[method](url)
                .then(response => {
                    toastStore.add({
                        message: response.data.message,
                        type: response.data.type,
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
                            type: 'warning',
                        });
                    } else {
                        toastStore.add({
                            message: "Something went wrong.",
                            type: 'warning',
                        });
                    }
                    return false;
                });
            return true;
        },
        async toggleWatchLater(id, toggle) {
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
