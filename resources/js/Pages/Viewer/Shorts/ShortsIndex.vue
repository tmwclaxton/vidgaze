<script setup>
import { Head } from '@inertiajs/vue3';
import ShortsPlayer from "@/Pages/Viewer/Shorts/ShortsPlayer/ShortsPlayer.vue";
import ShortsPlayerSkeleton from "@/Pages/Viewer/Shorts/ShortsPlayer/ShortsPlayerSkeleton.vue";
import {nextTick, onBeforeMount, onBeforeUnmount, onMounted, onUnmounted, ref, watch} from "vue";
import { useInfiniteScroll, useVirtualList } from '@vueuse/core';

import {usePlayerStore} from "@/Stores/PlayerStore";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import {useQueueStore} from "@/Stores/QueueStore";

const name = 'Shorts'
const shorts = ref([]);
// this is the index of the short that is fully visible
const fullyVisibleIndex = ref(0);
const UpdateFullyVisibleIndex = (index) => {
    fullyVisibleIndex.value = index;
};



onMounted(async () => {
    // forget page position i.e. scroll to top
    window.history.scrollRestoration = 'manual';

    // destroy all players this doesn't remove the metadata as full destroy is false
    await usePlayerStore().destroyPlayers(false).then(async () => {
        // if short slug is in url, play that short
        const urlParams = new URLSearchParams(window.location.search);
        const firstShort = urlParams.get('short') || null;

        await fetchShorts(firstShort).finally(() => {
            watchAction(0);
        });
    });
});

const fetchShorts = async (first_video_slug = null) => {
    let shortsIds = [];
    shortsIds = (shorts.value.length > 0) ? shorts.value.map(short => short.id).join(',') : [];  // get ids of shorts that have already been loaded to prevent duplicates
    let videos = await useContentRoutesStore().getVideos("popular", 8,shortsIds, true, first_video_slug)
    if (videos === undefined || videos.length === 0) {
        console.log('no more shorts');
        return;
    }
    // concat if external_id isn't already in shorts
    shorts.value = shorts.value.concat(videos.filter(video => !shorts.value.some(short => short.external_id === video.external_id)));
};

// this basically watches what index shorts is currently on and then calls the watchAction function
// the reason we call watchAction earlier is cause the doesn't update the index until it changes
watch(fullyVisibleIndex, (index) => {
    // console.log(['current short: ', index])
    watchAction(index);
    // if fullyVisibleIndex is above last 3 shorts, fetch more shorts
    if (index >= shorts.value.length - 3) {
        fetchShorts();
    }
});


async function watchAction(index, i = 0) {
    if (shorts.value === undefined || shorts.value.length === 0) {
        setTimeout(() => {
            if (i > 6) {
                console.log('shorts is undefined or empty');
                return;
            } else {
                console.log('retrying watchAction')
                watchAction(index, i + 1);
            }
        }, 1000);
        return;
    }
    await buildPlayers().then(() => {
        playFullyVisiblePlayer();
    });
}

function createVisibleIndices() {

    let index = fullyVisibleIndex.value;
    let visibleIndices = [index];
    if (index === 0) {
        // if at start
        visibleIndices = [index, index + 1, index + 2, index + 3, index + 4];
    } else if (index === 1) {
        // if at start + 1
        visibleIndices =  [index - 1, index, index + 1, index + 2];
    } else if (index >= shorts.value.length - 1) {
        // if at end
        visibleIndices = [index - 2, index - 1, index , index + 1];
    } else {
        // if in middle
        visibleIndices =  [index - 2, index - 1, index, index + 1, index + 2];
    }

    // check visible indices are within bounds of shorts
    // if return what ever is within bounds
    visibleIndices = visibleIndices.filter(index => index >= 0 && index < shorts.value.length);

    // get external ids of shorts that should be visible
    // console.log(['These shorts should be loaded',  visibleIndices.map(index => shorts.value[index].external_id)]);
    return visibleIndices;

}

async function buildPlayers() {
    // make a list of the indices of the shorts that should be visible
    let visibleIndices = createVisibleIndices();

    // loop through the 3 players that should be built and check if player has been loaded
    // if not, load it using playerStore

    for (let i = 0; i < visibleIndices.length; i++) {
        let visibleIndex = visibleIndices[i];
        // load player if shorts is undefined
        if (shorts.value[visibleIndex] === undefined) {
            console.log('shorts is undefined');
            return;
        }

        let id = 'player_div_holder_' + shorts.value[visibleIndex].external_id; // external_id is the ref to the div
        let object = shorts.value[visibleIndex];
        let player = usePlayerStore().findPlayer(shorts.value[visibleIndex].external_id)

        // if a visible player doesn't exist built it
        if (!player.external_id || !player.built) {
            await usePlayerStore().buildPlayer(id, object, 0, false, false, true);
        } else {
            // if player exists but it's not the fully visible id, pause it
            if (player.external_id !== shorts.value[fullyVisibleIndex.value].external_id) {
                player.safeTogglePause();
            }
        }
    }

    //loop through the shorts and check if the player is visible
    for (let i = 0; i < shorts.value.length; i++) {
        if (!visibleIndices.includes(i)) {
            // if not the visible players, destroy it
            let player = usePlayerStore().findPlayer(shorts.value[i].external_id)

            if (player) {
                await usePlayerStore().destroyPlayer(shorts.value[i].external_id, true, true);
            }
        }
    }
}

function playFullyVisiblePlayer() {
    let player;
    player = usePlayerStore().findPlayer(shorts.value[fullyVisibleIndex.value].external_id);
    if (player) {
        console.log('PLAYING VISIBLE PLAYER ' + player.external_id);
        player.safeTogglePlay();
    } else {
        console.log('player not found');
    }
}

onUnmounted(() => {
    // destroy all players
    usePlayerStore().destroyPlayers(true, true).then(() => {
        useQueueStore().rebuildPlayer();
    });
});


</script>
<!--rerender everytime page reloads-->
<template >
    <div>
        <Head title="VidGaze Shorts" />

            <div id="customScrollDiv" class="max-h-[calc(100vh-4rem)] overflow-hidden duration-75  overflow-y-scroll  snap snap-y snap-mandatory ease-in-out">
                    <template v-if="shorts.length > 0">
                        <ShortsPlayer  v-for="(data, index) in shorts" :video="data" :index="index"
                                      @UpdateFullyVisibleIndex="UpdateFullyVisibleIndex(index)" :key="index"/>
                    </template>
                    <template v-else>
                        <ShortsPlayerSkeleton/>
                    </template>
            </div>

    </div>
</template>
