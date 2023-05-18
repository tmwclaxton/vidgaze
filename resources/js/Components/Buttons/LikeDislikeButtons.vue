<template>
        <div @click="toggleLike" class="select-none flex flex-col gap-1 cursor-pointer">
            <ThumbsUpIcon class="h-8 mx-auto" :class="likeButtonClasses" />
            <p class="font-bold text-sm text-center" v-text="video.likes" />
        </div>
        <div @click="toggleDislike" class="select-none flex flex-col gap-1 cursor-pointer">
            <ThumbsDownIcon class="h-8 mx-auto" :class="dislikeButtonClasses" />
            <p class="font-bold text-sm text-center" v-text="video.dislikes" />
        </div>
</template>

<script setup>
import ThumbsUpIcon from '~/images/icons/like.svg';
import ThumbsDownIcon from '~/images/icons/dislike.svg';
import { computed, onMounted, ref } from "vue";
import { useToastStore } from "@/Stores/ToastStore";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

const toastStore = useToastStore();
const name = 'LikeDislikeButtons';
const liked = ref(false);
const disliked = ref(false);
const props = defineProps({
    video: {
        type: Object,
        required: true
    }
});

const toggleLike = () => {
    // if not logged in, redirect to login page using ziggy
    if (usePage().props.auth.user === null) {
        window.location.href = route('login');
        return;
    }

    const videoId = props.video.id;
    const likeRoute = route('video.like.toggle', { videoId });

    // Send a POST request to the like route
    axios.post(likeRoute)
        .then(response => {
            // Handle the successful response
            // toastStore.add({
            //     message: response.data.message,
            //     type: 'success'
            // });

            if (response.data.result === "like") {
                props.video.likes++;
                liked.value = true;
                if (disliked.value) {
                    props.video.dislikes--;
                    disliked.value = false;
                }
            } else {
                props.video.likes--;
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

const toggleDislike = () => {
    // if not logged in, redirect to login page using ziggy
    if (usePage().props.auth.user === null) {
        window.location.href = route('login');
        return;
    }

    const videoId = props.video.id;
    const dislikeRoute = route('video.dislike.toggle', { videoId });

    // Send a POST request to the dislike route
    axios.post(dislikeRoute)
        .then(response => {
            // Handle the successful response
            // toastStore.add({
            //     message: response.data.message,
            //     type: 'success'
            // });

            if (response.data.result === "dislike") {
                props.video.dislikes++;
                disliked.value = true;
                if (liked.value) {
                    props.video.likes--;
                    liked.value = false;
                }
            } else {
                props.video.dislikes--;
                disliked.value = false;
            }
        })
        .catch(error => {
            // Handle the error response
            toastStore.add({
                message: 'Error disliking video',
                type: 'error'
            });
        });
};



const likeButtonClasses = computed(() => ({
    'text-blue-600': liked.value,
    '': !liked.value
}));

const dislikeButtonClasses = computed(() => ({
    'text-blue-600': disliked.value,
    '': !disliked.value
}));

onMounted(async () => {
    if (usePage().props.auth.user !== null) {
        // check if user has liked or disliked the video
        const videoId = props.video.id;
        try {
            const response = await axios.get(route('videos.view.info', {videoId: videoId}));
            const data = response.data;
            if (data.liked === "like") {
                liked.value = true;
            } else if (data.liked === "dislike") {
                disliked.value = true;
            }
        } catch (error) {
            console.log(error);
        }
    }

});
</script>
