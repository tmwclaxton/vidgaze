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
import { requireAuth } from '@/utils/authGate';
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
    requireAuth(() => {
        const likeRoute = route('api.podcast.love.toggle', { podcastId: props.podcast.id });

        axios.post(likeRoute)
            .then(response => {
                toastStore.add({
                    message: response.data.message,
                    type: response.data.type
                });

                if (response.data.result === "like") {
                    props.podcast.like_count++;
                    liked.value = true;
                } else {
                    props.podcast.like_count--;
                    liked.value = false;
                }
            })
            .catch(error => {
                toastStore.add({
                    message: 'Error liking video',
                    type: 'warning'
                });
            });
    });
};


onMounted(async () => {



});


</script>
