<template>
    <div @click="subscribe" :class="buttonClasses" class="w-max h-max cursor-pointer rounded-lg px-4 py-1 text-xs font-bold border ring-0 select-none">
        <p v-text="text" class=""></p>
    </div>
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import { useToastStore } from "@/Stores/ToastStore";
import {usePage} from "@inertiajs/vue3";
const toastStore = useToastStore();
const name = 'SubscribeButton'
const subscribed = ref(false);
const text = computed(() => subscribed.value ? 'Subscribed' : 'Subscribe');
const props = defineProps({
    channel: {
        type: Object,
        required: true
    }
});

const subscribe = () => {
    // if not logged in, redirect to login page using ziggy
    if ( usePage().props.auth.user === null) {
        window.location.href = route('login');
        return;
    }


    axios.post(route('channel.subscription.toggle', {channelId: props.channel.id}))
        .then(response => {
            // Handle successful subscription
            toastStore.add({
                message: response.data.message,
                type: response.data.type
            });
            // Add channel to subscriptions
            if (response.data.subscribed) {
                // Add channel to subscriptions
                usePage().props.auth.subscriptions.push(props.channel.id);
            } else {
                // Remove channel from subscriptions
                usePage().props.auth.subscriptions = usePage().props.auth.subscriptions.filter(subscription => subscription !== props.channel.id);
            }
            subscribed.value = !subscribed.value;
        })
        .catch(error => {
            // Handle subscription error
            toastStore.add({
                message: 'Something went wrong, please try again later',
                type: 'error'
            });
        });

};

onMounted(() => {
    // Check if user is subscribed to channel by checking in auth subscriptions
    if (usePage().props.auth.user !== null) {
        subscribed.value = usePage().props.auth.subscriptions.some(subscription => subscription === props.channel.id);
    }
});

const buttonClasses = computed(() => ({
    'bg-zinc-200 text-zinc-700 hover:bg-zinc-300 dark:bg-zinc-700 dark:text-white  border-transparent ': subscribed.value,
    ' border-red-200 dark:border-transparent dark:bg-red-700 hover:bg-red-100 dark:hover:bg-red-600 text-red-700 dark:text-white ': !subscribed.value
}));
</script>
