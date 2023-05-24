import { defineStore } from 'pinia'
import { usePlayerStore } from './PlayerModalStore.js'
export const useQueueStore = defineStore('QueueStore', {
    state: () => {
        return {
            items: [] ,
            index: 0,
        }
    },
    actions: {
        add(item) {
            // items is in the form of [[object, type], [[id:2,title:"asdf" etc.. ], "video"], ...]
            const isItemInArray = this.items.some(
                (existingItem) => existingItem.object.id === item.object.id
            );
            if (isItemInArray) {
                return false;
            } else {
                this.items.push({
                    object: item.object,
                    type: item.type,
                });
                if (this.items.length === 1) {
                    this.changeIndex(this.index);
                }
                return true;
            }

            // if no items in queue, set player modal store to this item


        },
        remove(id) {
          // items is in the form of [[id, type], [2, "video"], ...]
            for (let i = 0; i < this.items.length; i++) {
                if (this.items[i]['object'].id === id) {
                    this.items.splice(i, 1);
                    return true;
                }
            }
            return false;
        },
        changeIndex(index) {

            let playerModalStore = usePlayerStore();
            this.index = index;
            playerModalStore.show = true;
            // set player modal store to this item
            if (this.items.length > 0) {
                playerModalStore.destroyPlayers();
                playerModalStore.object = this.items[this.index]['object'];
                playerModalStore.type = this.items[this.index]['type'];
                playerModalStore.buildPlayer();
            }
        }
    }
})
