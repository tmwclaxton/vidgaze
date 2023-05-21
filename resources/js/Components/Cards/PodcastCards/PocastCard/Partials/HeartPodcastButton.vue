<template>
    <div   class="pointer-events-auto flex flex-row gap-x-2 absolute bottom-3 left-14 ml-1 opacity-0 group-hover:opacity-100 transition duration-300">
        <div class="rounded-full bg-white w-10 h-10 flex opacity-75" @click="toggleLike">
            <font-awesome-icon :icon="['fas', 'heart']" class="w-5 h-4 my-auto mx-auto" :class="classes"/>
        </div>
    </div>
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import { defineProps } from "vue";
import {usePage} from "@inertiajs/vue3";
import axios from "axios";

const name = 'HeartPodcastButton';
const props = defineProps({
    podcast: {
        type: Object,
        required: true
    }
});
let liked = ref(false);

const classes = computed(() => ({
    'text-red-500': liked.value,
    'text-black': !liked.value
}));
const toggleLike = () => {
    liked.value = !liked.value;
}

onMounted(async () => {
    if (usePage().props.auth.user !== null) {
        // check if user has liked or disliked the podcast
        const podcastId = props.podcast.id;
        try {
            const response = await axios.get(route('podcasts.view.interaction', {podcastId: podcastId}));
            const data = response.data;
            if (data.liked === "like") {
                liked.value = true;
            }
        } catch (error) {
            console.log(error);
        }
    }

});
</script>
