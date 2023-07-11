<script setup>
import ToastListItem from "@/Components/Toast/ToastListItem.vue";
import {onUnmounted, ref} from "vue";

import {Inertia} from "@inertiajs/inertia";
import {usePage} from "@inertiajs/vue3";
import { useToastStore } from '@/Stores/ToastStore.js'

const toastStore = useToastStore();

const page = usePage();

const props = defineProps({
    flash: {
        type: Object,
        required: false,
        default: null
    }
});
// toast.add({
//     message: 'test',
//     type: "error",
// });
let timeoutId;
let isToastMessageCalled = false;

function toastMessages() {
    if (!isToastMessageCalled) {
        if (props.flash.toast && props.flash.toast.length > 0) {
            toastStore.add({
                message: props.flash.toast,
                type: "normal",
            });
        }
        if (props.flash.error && props.flash.error.length > 0) {
            toastStore.add({
                message: props.flash.error,
                type: "warning",
            });
        }
        if (props.flash.success && props.flash.success.length > 0) {
            toastStore.add({
                message: props.flash.success,
                type: "success",
            });
        }
        if (props.flash.status && props.flash.status.length > 0) {
            toastStore.add({
                message: props.flash.status,
                type: "normal",
            });
        }
        isToastMessageCalled = true;
    }
}

let removeSuccessEventListener = Inertia.on("success", () => {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }
    timeoutId = setTimeout(() => {
        toastMessages();
        isToastMessageCalled = false;
    }, 50);
});
let removeNavigateEventListener = Inertia.on("navigate", () => {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }
    timeoutId = setTimeout(() => {
        toastMessages();
        isToastMessageCalled = false;
    }, 50);
});

onUnmounted(() => {
    removeSuccessEventListener();
    removeNavigateEventListener();
});

function remove(index) {
    toastStore.remove(index);
}
</script>
<template>
    <TransitionGroup
        tag="div"
        enter-from-class="translate-x-full opacity-0"
        enter-active-class="duration-500"
        leave-active-class="duration-500"
        leave-to-class="translate-x-full opacity-0"
        class="fixed bottom-4 right-4 z-50 w-full max-w-xs space-y-4">
        <ToastListItem
            v-for="(item, index) in toastStore.items"
            :key="item.key"
            :message="item.message"
            :type="item.type"
            :duration="3000"
            @remove="remove(index)"
        />
    </TransitionGroup>
</template>
