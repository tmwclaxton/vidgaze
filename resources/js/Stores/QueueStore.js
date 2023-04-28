import { defineStore } from 'pinia'

export const useQueueStore = defineStore('QueueStore', {
    state: () => {
        return {
            items: [] ,
            index: 0,
        }
    },
    actions: {
        add(item) {
            // items is in the form of [[id, type], [2, "video"], ...]
            const isItemInArray = this.items.some(
                (existingItem) => existingItem.id === item.id
            );
            if (isItemInArray) {
                return false;
            } else {
                this.items.push({
                    id: item.id,
                    key: Symbol(),
                    type: item.type,
                });
                return true;
            }
        },
        remove(id) {
          // items is in the form of [[id, type], [2, "video"], ...]
            for (let i = 0; i < this.items.length; i++) {
                if (this.items[i]['id'] === id) {
                    this.items.splice(i, 1);
                    return true;
                }
            }
            return false;
        },
        changeIndex(index) {
            this.index = index;
        }
    }
})
