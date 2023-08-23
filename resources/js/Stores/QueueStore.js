import { defineStore } from 'pinia'
import { usePlayerStore } from './PlayerStore.js'
import {usePage} from "@inertiajs/vue3";
export const useQueueStore = defineStore('QueueStore', {
    state: () => {
        return {
            items: [] ,
            index: 0,
            playlist: null,
            autoplay: false,
            refreshMiniPlayer: "",
        }
    },
    getters: {
        currentItem() {
            return this.items[this.index];
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
            return queueStore.items !== undefined && queueStore.items.length > 0 && usePage().url !== '/shorts' && !route().current('watch.show');
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
                this.items.push(item);
                // if this is the first item in the queue, play it
                if (this.items.length === 1) {
                    this.changeIndex(this.index);
                }
                return true;
            }


        },

        removeAll() {
            // do a full destroy for each item in the queue
            for (let i = 0; i < this.items.length; i++) {
                usePlayerStore().destroyPlayer(this.items[i].external_id, true).then(r =>  {
                    this.items = [];
                    this.index = 0;
                });
            }
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

        changeIndexByExternalID(external_id) {
            for (let i = 0; i < this.items.length; i++) {
                if (this.items[i].external_id === external_id) {
                    this.changeIndex(i);
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
            if (this.items.length > 0) {
                usePlayerStore().destroyPlayers().then(r => {
                    usePlayerStore().buildPlayer(playerDivHolderID, this.items[this.index], 0, true, true).then(r => {
                        console.log("miniplayer player built");
                    });
                });
            }
        }
    }
})
