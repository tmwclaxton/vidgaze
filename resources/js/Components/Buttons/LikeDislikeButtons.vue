<template>
        <div @click="toggleLike" class="select-none flex gap-1 cursor-pointer justify-center "
             :class="[props.orientationVertical ? 'flex-col' : 'flex-row gap-x-3 ']">
            <ThumbsUpIcon class="transform transition-all duration-200  my-auto flex-shrink-0"  :class="[ props.orientationVertical ? 'mx-auto h-8' : 'h-6', likeButtonClasses]"  />
            <p class="font-bold text-sm text-center my-auto" v-text="video.likes" />
        </div>

        <!--vertical hr-->
        <hr v-if="!props.orientationVertical" class="border border-zinc-400 dark:border-gray-700 w-0.5 h-8 rounded" />

        <div @click="toggleDislike" class="select-none flex gap-1 cursor-pointer justify-center "
             :class="[props.orientationVertical ? 'flex-col' : 'flex-row gap-x-3']">
            <!--combine likeButtonClass and the props.ortientaitonVertical classes-->
            <ThumbsDownIcon class="transform transition duration-200 my-auto   " :class="[ props.orientationVertical ? 'mx-auto h-8' : 'w-6 h-6', dislikeButtonClasses]" />
            <p class="font-bold text-sm text-center my-auto " v-text="video.dislikes" />
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
        required: false,
        default: {
            likes: 0,
            dislikes: 0
        }
    },
    comment: {
        type: Object,
        required: false
    },
    orientationVertical: {
        type: Boolean,
        required: true,
        default: false
    },
    value: {
        type: String,
        required: false,
        default: null
    }
});

const toggleLike = () => {
    // if not logged in, redirect to login page using ziggy
    if (usePage().props.auth.user === null) {
        window.location.href = route('login');
        return;
    }
    let likeRoute = '';
    if (props.comment === undefined) {
        likeRoute = route('video.like.toggle', { videoId: props.video.id  });
    } else {
        likeRoute = route('comment.like.toggle', { commentId: props.comment.id  });
    }

    // Send a POST request to the like route
    axios.post(likeRoute)
        .then(response => {
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
            if (error.response.status === 401) {
                window.location.href = route('login');
            }
        });
};

const toggleDislike = () => {
    // if not logged in, redirect to login page using ziggy
    if (usePage().props.auth.user === null) {
        window.location.href = route('login');
        return;
    }

    let dislikeRoute = '';
    if (props.comment === undefined) {
        dislikeRoute = route('video.dislike.toggle', { videoId: props.video.id  });
    } else {
        dislikeRoute = route('comment.dislike.toggle', { commentId: props.comment.id  });
    }

    // Send a POST request to the dislike route
    axios.post(dislikeRoute)
        .then(response => {

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
            if (error.response.status === 401) {
                window.location.href = route('login');
            }
        });
};



const likeButtonClasses = computed(() => ({
    'text-blue-600': liked.value,
    '': !liked.value
}));

const dislikeButtonClasses = computed(() => ({
    'text-red-600': disliked.value,
    '': !disliked.value
}));

onMounted(async () => {
    if (usePage().props.auth.user !== null && props.value === null) {
        // check if user has liked or disliked the video
        const videoId = props.video.id;
        try {
            const response = await axios.get(route('video.interaction', {videoId: videoId}));
            const data = response.data;
            if (data.liked === "like") {
                liked.value = true;
            } else if (data.liked === "dislike") {
                disliked.value = true;
            }
        } catch (error) {
            console.log(error);
        }
    } else {
        // 0 both unselected, 1 like button, 2 dislike button
        if (props.value.value === 1) {
            liked.value = true;
        } else if (props.value.value === 2) {
            disliked.value = true;
        }
    }

});
</script>
