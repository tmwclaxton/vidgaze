import {reactive} from "vue";

export default reactive({
    items: [],
    add(toast) {
        //add at end of array
        this.items.push({
            key: Symbol(),
            ...toast
        });

        // this.items.unshift({
        //     key: Symbol(),
        //     ...toast
        // });
    },
    remove(index) {
        this.items.splice(index, 1);
    },
});
