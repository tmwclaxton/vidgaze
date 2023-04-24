import { reactive } from "vue";

const itemStore = reactive({
    itemId: null,
    setItemId(id) {
        this.itemId = id;
    },
    getItemId() {
        return this.itemId;
    },
});

export default itemStore;
