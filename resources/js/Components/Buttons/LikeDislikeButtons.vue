<template>
        <div @click="toggleLike" class=" flex gap-1 cursor-pointer justify-center "
             :class="[props.orientationVertical ? 'flex-col' : 'flex-row gap-x-3 ']">
            <ThumbsUpIcon class="transform transition-all duration-200  my-auto flex-shrink-0"  :class="[ props.orientationVertical ? 'mx-auto h-8' : 'h-6', likeButtonClasses]"  />
            <p class="font-bold text-sm text-center my-auto" v-text="itemHandler.like_count ?? 0" />
        </div>

        <!--vertical hr-->
        <hr v-if="!props.orientationVertical" class="w-0.5 h-8 rounded border-0 bg-gradient-to-b from-transparent via-cyan-400/35 to-transparent dark:via-fuchsia-400/30 opacity-80 transition ease-in-out" />

        <div @click="toggleDislike" class=" flex gap-1 cursor-pointer justify-center "
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
import { requireAuth, openLoginModal } from '@/utils/authGate';

const toastStore = useToastStore();
const name = 'LikeDislikeButtons';
const liked = ref(false);
const disliked = ref(false);
const props = defineProps({
    item: {
        type: Object,
        required: false,
        default: null
    },
    comment: {
        type: Object,
        required: false,
        default: null
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
    if (props.comment !== null) {
        return props.comment;
    } else if (props.item !== null) {
        return props.item;
    } else {
        return null;
    }
});

const runToggleLike = () => {
    let likeRoute = '';
    if (props.comment === null) {
        likeRoute = route('api.video.like.toggle', { video_id: props.item.id });
    } else {
        likeRoute = route('api.comment.like.toggle', { item_id: props.item.id, item_type: props.item.type, comment_id: props.comment.id });
    }

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
            if (error.response?.status === 401) {
                openLoginModal(runToggleLike);
            }
        });
};

const toggleLike = () => {
    requireAuth(runToggleLike);
};

const runToggleDislike = () => {
    let dislikeRoute = '';
    if (props.comment === null) {
        if (props.item.type === 'video') {
            dislikeRoute = route('api.video.dislike.toggle', { video_id: itemHandler.value.id  });
        }
    } else {
        dislikeRoute = route('api.comment.dislike.toggle', {  item_id: props.item.id, item_type: 'video', comment_id: props.comment.id   });
    }

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
            if (error.response?.status === 401) {
                openLoginModal(runToggleDislike);
            }
        });
};

const toggleDislike = () => {
    requireAuth(runToggleDislike);
};



const likeButtonClasses = computed(() => ({
    'text-cyan-500 dark:text-cyan-400 drop-shadow-[0_0_10px_rgba(34,211,238,0.55)] scale-105': liked.value,
    'text-zinc-500 dark:text-zinc-400 hover:text-cyan-400/90 hover:drop-shadow-[0_0_8px_rgba(34,211,238,0.35)]': !liked.value,
}));

const dislikeButtonClasses = computed(() => ({
    'text-rose-600 dark:text-rose-400 drop-shadow-[0_0_10px_rgba(251,113,133,0.45)] scale-105': disliked.value,
    'text-zinc-500 dark:text-zinc-400 hover:text-rose-400/90 hover:drop-shadow-[0_0_8px_rgba(251,113,133,0.35)]': !disliked.value,
}));

onMounted(async () => {
    setTimeout(async () => {
        if (useAuthStore().user !== null && props.item !== null && props.comment === null ) {
            const videoId = props.item.id;
            try {
                const response = await axios.get(route('api.video.interaction', {video_id: videoId}));
                const data = response.data.interaction;
                if (data !== null) {
                    if (data.liked === "like") {
                        liked.value = true;
                    } else if (data.liked === "dislike") {
                        disliked.value = true;
                    }
                }
            } catch (error) {
                console.log(error);
            }
        } else {
            // this is how comments set the like & dislike values as they get interactions in a batch
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
