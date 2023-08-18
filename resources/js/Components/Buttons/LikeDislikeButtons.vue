<template>
        <div @click="toggleLike" class="select-none flex gap-1 cursor-pointer justify-center "
             :class="[props.orientationVertical ? 'flex-col' : 'flex-row gap-x-3 ']">
            <ThumbsUpIcon class="transform transition-all duration-200  my-auto flex-shrink-0"  :class="[ props.orientationVertical ? 'mx-auto h-8' : 'h-6', likeButtonClasses]"  />
            <p class="font-bold text-sm text-center my-auto" v-text="itemHandler.like_count ?? 0" />
        </div>

        <!--vertical hr-->
        <hr v-if="!props.orientationVertical" class="border border-zinc-300 group-hover:border-zinc-400 dark:border-gray-700 dark:group-hover:border-zinc-700 w-0.5 h-8 rounded transition ease-in-out" />

        <div @click="toggleDislike" class="select-none flex gap-1 cursor-pointer justify-center "
             :class="[props.orientationVertical ? 'flex-col' : 'flex-row gap-x-3']">
            <!--combine likeButtonClass and the props.ortientaitonVertical classes-->
            <ThumbsDownIcon class="transform transition duration-200 my-auto   " :class="[ props.orientationVertical ? 'mx-auto h-8' : 'w-6 h-6', dislikeButtonClasses]" />
            <p class="font-bold text-sm text-center my-auto " v-text="itemHandler.dislike_count ?? 0" />
        </div>
</template>

<script setup>
import ThumbsUpIcon from '~/images/icons/like.svg';
import ThumbsDownIcon from '~/images/icons/dislike.svg';
import { computed, onMounted, ref } from "vue";
import { useToastStore } from "@/Stores/ToastStore";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import {useAuthStore} from "@/Stores/AuthStore";

const toastStore = useToastStore();
const name = 'LikeDislikeButtons';
const liked = ref(false);
const disliked = ref(false);
const props = defineProps({
    item: {
        type: Object,
        required: false,
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

    setLikeValue: {
        type: Number,
        required: false,
        default: null
    },

});
const itemHandler = computed(() => {
    if (props.comment !== undefined) {
        return props.comment;
    } else if (props.item !== undefined) {
        return props.item;
    } else {
        return null;
    }
});

const toggleLike = () => {
    // if not logged in, redirect to login page using ziggy
    if (useAuthStore().user === null) {
        window.location.href = route('login');
        return;
    }
    let likeRoute = '';
    if (props.comment === undefined) {
        likeRoute = route('api.video.like.toggle', { video_id: props.item.id });
    } else {
        likeRoute = route('api.comment.like.toggle', { item_id: props.item.id, item_type: props.item.type, comment_id: props.comment.id });
    }

    // Send a POST request to the like route
    axios.post(likeRoute)
        .then(response => {
            if (response.data.result === "like") {
                itemHandler.value.like_count++;
                liked.value = true;
                if (disliked.value) {
                    itemHandler.value.dislike_count--;
                    disliked.value = false;
                }
            } else {
                itemHandler.value.like_count--;
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
    if (useAuthStore().user === null) {
        window.location.href = route('login');
        return;
    }

    let dislikeRoute = '';
    if (props.comment === null) {
        if (props.item.type === 'video') {
            dislikeRoute = route('api.video.dislike.toggle', { video_id: itemHandler.value.id  });
        }

    } else {
        dislikeRoute = route('api.comment.dislike.toggle', {  item_id: props.item.id, item_type: 'video', comment_id: props.comment.id   });
    }

    // Send a POST request to the dislike route
    axios.post(dislikeRoute)
        .then(response => {

            if (response.data.result === "dislike") {
                itemHandler.value.dislike_count++;
                disliked.value = true;
                if (liked.value) {
                    itemHandler.value.like_count--;
                    liked.value = false;
                }
            } else {
                itemHandler.value.dislike_count--;
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
    setTimeout(async () => {
        if (useAuthStore().user !== null && props.item !== undefined && props.comment === undefined) {
            const videoId = props.item.id;
            try {
                const response = await axios.get(route('api.video.interaction', {video_id: videoId}));
                const data = response.data.interaction;
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
            if (props.setLikeValue === 1) {
                liked.value = true;
            } else if (props.setLikeValue === 2) {
                disliked.value = true;
            }
        }
    }, 500);

});
</script>
