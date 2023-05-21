<template>
    <div  class="pointer-events-auto flex flex-row gap-x-2 absolute bottom-3 left-14 ml-1 opacity-0 group-hover:opacity-100 transition duration-300">
        <div class="rounded-full bg-white w-10 h-10 flex opacity-75" @click="toggleLike">
            <font-awesome-icon :icon="['fas', 'heart']" class="w-5 h-4 my-auto mx-auto" :class="classes"/>
        </div>
    </div>
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import { defineProps } from "vue";
import {usePage} from "@inertiajs/vue3";
import axios from "axios";
import {useToastStore} from "@/Stores/ToastStore";
const toastStore = useToastStore();

const name = 'HeartPodcastButton';
const props = defineProps({
    podcast: {
        type: Object,
        required: true
    },
    liked: {
        type: Boolean,
        required: true
    }
});
let liked = ref(false);
watch(() => props.liked, (newValue) => {
    liked.value = newValue;
});

const classes = computed(() => ({
    'text-red-500': liked.value,
    'text-black': !liked.value
}));

const toggleLike = () => {
    // if not logged in, redirect to login page using ziggy
    if (usePage().props.auth.user === null) {
        window.location.href = route('login');
        return;
    }

    const likeRoute = route('podcast.like.toggle', {  podcastId: props.podcast.id  });

    // Send a POST request to the like route
    axios.post(likeRoute)
        .then(response => {
            // Handle the successful response
            toastStore.add({
                message: response.data.message,
                type: response.data.type
            });

            if (response.data.result === "like") {
                props.podcast.likes++;
                liked.value = true;
            } else {
                props.podcast.likes--;
                liked.value = false;
            }
        })
        .catch(error => {
            // Handle the error response
            toastStore.add({
                message: 'Error liking video',
                type: 'error'
            });
        });
};


onMounted(async () => {



});


</script>
