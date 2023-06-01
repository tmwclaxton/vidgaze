import { defineStore } from 'pinia'
import { usePlayerStore } from './PlayerStore.js'
export const useQueueStore = defineStore('QueueStore', {
    state: () => {
        return {
            debug: true,
            items: [] ,
            index: 0,
        }
    },
    getters: {
        currentItem() {
            return this.items[this.index];
        }
    },
    actions: {
        debugMessage(message) {
            if (this.debug) {
                console.log(message);
            }
        },

        inQueue(id, type) {
            // items is in the form of [[object, type], [{id:2, ...}, "video"], ...]
            for (let i = 0; i < this.items.length; i++) {
                if (this.items[i]['object'].id === id && this.items[i]['type'] === type) {
                    return true;
                }
            }
            return false;
        },

        add(item) {
            // items is in the form of [[object, type], [[id:2,title:"asdf" etc.. ], "video"], ...]
            const isItemInArray = this.items.some(
                (existingItem) => existingItem.object.id === item.object.id && existingItem.type === item.type
            );
            if (isItemInArray) {
                return false;
            } else {
                this.items.push({
                    object: item.object,
                    type: item.type,
                });
                // if this is the first item in the queue, play it
                if (this.items.length === 1) {
                    this.changeIndex(this.index);
                }
                return true;
            }


        },
        remove(id, type) {
          // items is in the form of [[object, type], [{id:2, ...}, "video"], ...]
            for (let i = 0; i < this.items.length; i++) {

                // if the item is in the queue
                if (this.items[i]['object'].id === id && this.items[i]['type'] === type) {
                    let changeIndexBool = false
                    // if I delete the current item, I need to change the video
                    if (id === this.items[this.index]['object'].id && type === this.items[this.index]['type']) {
                        changeIndexBool = true;
                    }

                    // if I delete an item that is greater or equal to the current index, I need to decrement the index
                    //
                    if (i >= this.index && this.index > 0) {
                        this.debugMessage("decrementing index from " + this.index + " to " + (this.index - 1) + "decrement index");
                        this.index = this.index - 1;
                    }

                    if (changeIndexBool) {
                        this.debugMessage("changing index to " + this.index + "changeIndexBool");
                        this.changeIndex(this.index);
                    }

                    this.debugMessage("removing item");
                    this.items.splice(i, 1);


                    return true;
                }
            }

            return false;
        },
        changeIndex(index) {

            let playerModalStore = usePlayerStore();

            this.debugMessage("changing index from " + this.index + " to " + index);
            this.index = index;
            this.debugMessage(index);
            this.debugMessage( this.items[index]['object'].id  );
            playerModalStore.show = true;
            // set player modal store to this item
            if (this.items.length > 0) {
                playerModalStore.destroyPlayers();
                playerModalStore.autoplay = true;
                playerModalStore.object = this.items[this.index]['object'];
                playerModalStore.type = this.items[this.index]['type'];
                playerModalStore.buildPlayer();
            }
        }
    }
})
