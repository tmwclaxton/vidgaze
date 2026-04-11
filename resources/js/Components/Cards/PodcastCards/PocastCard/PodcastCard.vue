<script setup>
import HeartPodcastButton from "@/Components/Cards/PodcastCards/PocastCard/Partials/HeartPodcastButton.vue";
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { storeToRefs } from 'pinia';
import { computed, ref } from 'vue';

import { useAuthStore } from '@/Stores/AuthStore';
import { usePodcastPlayerStore } from '@/Stores/PodcastPlayerStore';

const name = 'PodcastCard';
const props = defineProps({
    podcast: {
        type: Object,
        required: true,
    },
});

const authStore = useAuthStore();
const podcastPlayer = usePodcastPlayerStore();
const { isPlaying, podcast: playerPodcast } = storeToRefs(podcastPlayer);

const checked = ref(false);
const liked = ref(false);
const playLoading = ref(false);

const isThisPodcastActive = computed(() => playerPodcast.value?.slug === props.podcast.slug);

const getPodcastInfo = async () => {
    if (authStore.user !== null && !checked.value) {
        try {
            const response = await axios.get(route('api.podcast.interaction', { podcastId: props.podcast.id }));
            const data = response.data;
            checked.value = true;
            if (data.liked === 'like') {
                liked.value = true;
            }
        } catch (error) {
            console.log(error);
        }
    }
};

const onPlayClick = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (playLoading.value) {
        return;
    }
    if (isThisPodcastActive.value && podcastPlayer.hasTrack) {
        podcastPlayer.togglePlay();
        return;
    }

    playLoading.value = true;
    try {
        const res = await axios.get(route('api.podcast.latest-episode', { slug: props.podcast.slug }));
        const ep = res.data?.data ?? res.data;
        if (!ep?.audio_url) {
            return;
        }
        podcastPlayer.playTrack(props.podcast, ep);
    } catch (error) {
        if (error.response?.status !== 404) {
            console.error(error);
        }
    } finally {
        playLoading.value = false;
    }
};
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

                <div class="pointer-events-auto absolute bottom-3 left-3 flex flex-row gap-x-2">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-white opacity-90 shadow-md transition hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-60"
                        :aria-label="
                            playLoading
                                ? 'Loading episode'
                                : isThisPodcastActive && isPlaying
                                  ? 'Pause podcast'
                                  : 'Play latest episode'
                        "
                        :disabled="playLoading"
                        @click="onPlayClick"
                    >
                        <font-awesome-icon
                            v-if="playLoading"
                            :icon="['fas', 'spinner']"
                            class="h-4 w-4 animate-spin text-zinc-900"
                        />
                        <font-awesome-icon
                            v-else-if="isThisPodcastActive && isPlaying"
                            :icon="['fas', 'pause']"
                            class="h-4 w-4 text-zinc-900 drop-shadow-[0_0_6px_rgba(34,211,238,0.65)]"
                        />
                        <font-awesome-icon
                            v-else
                            :icon="['fas', 'play']"
                            class="h-4 w-5 pl-1 text-zinc-900 drop-shadow-[0_0_6px_rgba(34,211,238,0.65)]"
                        />
                    </button>
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
