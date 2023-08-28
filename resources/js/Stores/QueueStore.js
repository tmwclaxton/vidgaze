import { defineStore } from 'pinia'
import { usePlayerStore } from './PlayerStore.js'
import {router, usePage} from "@inertiajs/vue3";
import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
export const useQueueStore = defineStore('QueueStore', {
    state: () => {
        return {
            items: [] ,
            index: 0,
            // autoplay: false,
            refreshMiniPlayer: "", // used to refresh some front end components

            playlist: null,
            shuffle: false,
            page: 1,
            perPage: 20,
            playlistLoading: false,
        }
    },
    getters: {
        currentItem() {
            return this.items[this.index];
        },
        nextItem() {
            if (this.items.length === 0 || this.items.length === this.index + 1) {
                return null;
            }
            return this.items[this.index + 1];
        },
        currentPlayer() {
            if (this.currentItem === undefined) {
                return null;
            }
            return usePlayerStore().findPlayer(this.currentItem.external_id);
        },
        showMiniPlayer() {
            // Compute the value of showMiniPlayer based on your logic
            // For example, you can check if players array is not empty
            let queueStore = useQueueStore();
            // also depends on what page you are on ... // url doesn't contian shorts or watch
            return queueStore.items !== undefined && queueStore.items.length > 0 && usePage().url !== '/shorts' && !route().current('watch.show') && !queueStore.playlistLoading;
        },
        positionText() {
            if (this.items.length === 0) {
                return "";
            }
            const itemLength = this.playlist !== null ? this.playlist.video_count : this.items.length;
            return (this.index + 1) + ' / ' + itemLength;
        }
    },
    actions: {
        inQueue(external_id) {
            if (this.items.length === 0) {
                return false;
            }
            for (let i = 0; i < this.items.length; i++) {
                if (this.items[i].external_id === external_id) {
                    return true;
                }
            }
            return false;
        },

        add(item) {
            const isItemInArray = this.items.some(
                (existingItem) => existingItem.external_id === item.external_id
            );
            if (isItemInArray) {
                return false;
            } else {
                // if your adding a video on the watch page you want to finish the current video before playing the next one
                if (route().current('watch.show') && usePlayerStore().players.length === 1) {
                   this.items.push(usePlayerStore().players[0].object);
                }
                this.items.push(item);
                // if this is the first item in the queue, play it
                if (this.items.length === 1) {
                    this.changeIndex(this.index);
                }
                return true;
            }
        },

        removeAll(fullDestroy = false) {
            // confirm that the user wants to close the mini player as it will destroy the queue
            useConfirmModalStore().buttonOneText = 'Cancel';
            useConfirmModalStore().buttonTwoText = 'Delete';
            useConfirmModalStore().title = 'Are you sure, this will delete the queue?';
            useConfirmModalStore().show = true;
            useConfirmModalStore().continue = () => {
                // do a full destroy for each item in the queue
                for (let i = 0; i < this.items.length; i++) {
                    if (fullDestroy) {
                        usePlayerStore().destroyPlayer(this.items[i].external_id, true).then(r =>  {
                            this.clearQueue();
                        });
                    } else {
                        this.clearQueue();
                    }
                }
            };
        },

        clearQueue() {
            this.items = [];
            this.index = 0;
            this.playlist = null;
            this.shuffle = false;
            this.page = 1;
            this.perPage = 20;
            this.playlistLoading = false;
        },

        remove(external_id) {
            for (let i = 0; i < this.items.length; i++) {
                // if the item is not in the queue
                if (this.items[i].external_id !== external_id) {
                    console.log("item not in queue");
                    continue;
                }

                let changeIndexBool = false
                // if I delete the current item, I need to change the video
                if (external_id === this.items[this.index].external_id && this.items.length > 1) {
                    changeIndexBool = true;
                }

                //find the player in the player store and destroy it
                usePlayerStore().destroyPlayer(this.items[i].external_id, true).then(r =>  {
                    // if I delete an item less than or equal to the current index, I need to decrement the index
                    if (i <= this.index && this.index > 0) {
                        console.log("decrementing index from " + this.index + " to " + (this.index - 1) + "decrement index");
                        this.index = this.index - 1;
                    }
                    if (changeIndexBool) {
                        console.log("changing index to " + this.index + "changeIndexBool");
                        this.changeIndex(this.index);
                    }
                    console.log("removing item");
                    this.items.splice(i, 1);
                    return true;
                });

            }
            return false;
        },

        rebuildPlayer() {
            // watch showMiniPlayer if it is changed to true check if queueStore has any items if so then build the player
            if (this.currentPlayer === false || this.currentPlayer === null) {
                return;
            }
            this.currentPlayer.endScreen = false;
            // if the video that was playing was in the queue get time and rebuild player with time in mini player
            if (this.currentItem.external_id !== null) {
                usePlayerStore().buildPlayer('miniplayer_div_holder', this.currentItem, this.currentPlayer.currentTime, true, false).then(r => {
                });
            }
        },

        setIndexByExternalID(external_id) {
            console.log("setIndexByExternalID");
            for (let i = 0; i < this.items.length; i++) {
                if (this.items[i].external_id === external_id) {
                    console.log("setting index to " + i);
                    this.index = i;
                }
            }
        },

        changeIndex(index) {
            let playerDivHolderID = null;
            if (this.showMiniPlayer) {
                playerDivHolderID = 'miniplayer_div_holder';
            } else {
                playerDivHolderID = 'watch_player';
            }
            this.index = index;
            usePlayerStore().show = true;
            // set player modal store to this item
            if (this.items.length === 0) {
                return;
            }

            usePlayerStore().destroyPlayers().then(r => {
                if (route().current('watch.show')) {
                    router.visit(route('watch.show', {slug: this.items[this.index].slug}))
                } else {
                    usePlayerStore().buildPlayer(playerDivHolderID, this.items[this.index], 0, true, true).then(r => {
                            console.log("miniplayer player built");
                        });
                }
            });
        },

        async paginate() {
            if (this.playlist !== null && this.index >= this.items.length - 5 && this.items.length === (this.page * this.perPage) ) {
                // console.log("paginating " + this.page);
                this.page = this.page + 1;
                let newItems = await usePlaylistModalStore().getPlaylist(this.playlist.slug, this.page, this.perPage);
                this.items = this.items.concat(newItems[1]);
            }
        }

    }
})
