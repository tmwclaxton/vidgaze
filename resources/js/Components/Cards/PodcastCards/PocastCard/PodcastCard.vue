
<script setup>
import HeartPodcastButton from "@/Components/Cards/PodcastCards/PocastCard/Partials/HeartPodcastButton.vue";
import {Link} from '@inertiajs/vue3';
import axios from "axios";
import {ref} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";

const name = 'PodcastCard';
const props = defineProps({
    podcast: {
        type: Object,
        required: true
    }
});

let checked = ref(false);
let liked = ref(false);
const getPodcastInfo = async () => {
    if (useAuthStore().user !== null && !checked.value) {
        try {
            const response = await axios.get(route('api.podcast.interaction', {podcastId: props.podcast.id}));
            const data = response.data;
            checked.value = true;
            if (data.liked === "like") {
                liked.value = true;
            }
        } catch (error) {
            console.log(error);
        }
    }
}
</script>

<template>
    <div @mouseenter="getPodcastInfo" class="h-full w-full cursor-pointer rounded">
        <div
            class="group relative rounded-lg ring-1 ring-transparent transition-all duration-300 hover:ring-emerald-400/35 hover:shadow-[0_0_24px_-12px_rgba(52,211,153,0.2)]"
        >
            <Link :href="route('podcast.show', { slug: podcast.slug })">
                <img class="block aspect-square w-full rounded" v-bind:src="podcast.thumbnail_url" />
            </Link>
            <div class="absolute bg-black rounded pointer-events-none
        bg-opacity-0 group-hover:bg-opacity-40 group-hover:opacity-100
        w-full h-full top-0 flex items-center transition justify-evenly duration-300">

                <div class="pointer-events-auto flex flex-row gap-x-2 absolute bottom-3 left-3 ">
                    <div class="rounded-full bg-white w-10 h-10 flex opacity-75">
                        <font-awesome-icon
                            :icon="['fas', 'play']"
                            class="mx-auto my-auto h-4 w-5 pl-1 text-zinc-900 drop-shadow-[0_0_6px_rgba(34,211,238,0.65)]"
                        />
                    </div>
                </div>
                <HeartPodcastButton :podcast="podcast" :liked="liked" />

            </div>
        </div>
        <Link :href="route('podcast.show', { slug: podcast.slug })">
            <div class="p-2 px-2">
                <h3
                    class="text-md font-bold text-emerald-700 drop-shadow-[0_0_12px_rgba(52,211,153,0.15)] dark:text-emerald-300"
                    v-html="podcast.title"
                ></h3>
            </div>
        </Link>
    </div>

</template>
