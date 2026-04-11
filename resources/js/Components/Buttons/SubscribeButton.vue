<template>
    <div v-if="!useAuthStore().user || useAuthStore().user.creator.slug !== props.channel.slug"
        @click="subscribe" :class="buttonClasses" class="w-max h-max cursor-pointer rounded-lg px-4 py-1 text-xs font-bold border">
        <p v-text="text" class=""></p>
    </div>
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import { useToastStore } from "@/Stores/ToastStore";
import {useAuthStore} from "@/Stores/AuthStore";
import { requireAuth } from '@/utils/authGate';
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
    requireAuth(() => {
    axios.post(route('api.creator.subscription.toggle', {channelId: props.channel.id}))
        .then(response => {
            // Handle successful subscription
            toastStore.add({
                message: response.data.message,
                type: response.data.toastType
            });
            // Add channel to subscriptions
            if (response.data.subscribed) {
                // Add channel to subscriptions
                useAuthStore().subscription_ids.push(props.channel.id);
            } else {
                // Remove channel from subscriptions
                useAuthStore().subscription_ids = useAuthStore().subscription_ids.filter(subscription => subscription !== props.channel.id);
            }
            subscribed.value = !subscribed.value;
        })
        .catch(error => {
            // Handle subscription error
            toastStore.add({
                message: 'Something went wrong, please try again later',
                type: 'warning'
            });
        });
    });
    };

onMounted(() => {
    subscribed.value = isSubscribed();
    watch(() => useAuthStore().subscription_ids, () => {
        // Check if user is subscribed to channel by checking in auth subscriptions
        subscribed.value = isSubscribed();
    });
});

const isSubscribed = () => {
    if (useAuthStore().user !== null) {
        return useAuthStore().subscription_ids.some(subscription => subscription === props.channel.id);
    } else {
        return false;
    }

}

const buttonClasses = computed(() => ({
    'bg-zinc-200 text-zinc-700 hover:bg-zinc-300 dark:bg-zinc-700 dark:text-white border-transparent ring-1 ring-emerald-400/35 shadow-[0_0_12px_-6px_rgba(52,211,153,0.25)] transition-all duration-200 ':
        subscribed.value,
    'border-red-200 dark:border-transparent dark:bg-red-700 hover:bg-red-100 dark:hover:bg-red-600 text-red-700 dark:text-white ring-1 ring-rose-400/40 shadow-[0_0_14px_-6px_rgba(244,114,182,0.3)] hover:shadow-[0_0_20px_-4px_rgba(248,113,113,0.4)] transition-all duration-200 ':
        !subscribed.value,
}));
</script>
