<script setup>
import { Head } from '@inertiajs/vue3';
import ShortsPlayer from "@/Pages/Viewer/Shorts/ShortsPlayer/ShortsPlayer.vue";
import ShortsPlayerSkeleton from "@/Pages/Viewer/Shorts/ShortsPlayer/ShortsPlayerSkeleton.vue";
import {onMounted, onUnmounted, ref, toRaw, watch} from "vue";
import { useInfiniteScroll, useVirtualList, useIntersectionObserver } from '@vueuse/core';

import {usePlayerStore} from "@/Stores/PlayerStore";
import {debounce} from "lodash";
import {useCommentSectionStore} from "@/Stores/CommentSectionStore";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";

const name = 'Shorts'
const shorts = ref([]);
// this is the index of the short that is fully visible
const fullyVisibleIndex = ref(0);
const { list, containerProps, wrapperProps } = useVirtualList(shorts, {
    itemHeight: window.innerHeight - 64,
    itemWidth: 400,
});
const UpdateFullyVisibleIndex = (index) => {
    fullyVisibleIndex.value = index;
};

useInfiniteScroll(
    containerProps.ref,
    async () => {
        await fetchShorts();
    },
    {
        distance: 2 * (window.innerHeight - 64), // load more when scrolled to within 2 shorts from the bottom
    }
)

onMounted(async () => {
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
    shorts.value = shorts.value.concat(videos);
};

// this basically watches what index shorts is currently on and then calls the watchAction function
// the reason we call watchAction earlier is cause the doesn't update the index until it changes
watch(fullyVisibleIndex, (index) => {
    // console.log(['current short: ', index])
    watchAction(index);
});


function watchAction(index, i = 0) {
    if (shorts.value === undefined || shorts.value.length === 0) {
        setTimeout(() => {
            if (i > 2) {
                console.log('shorts is undefined or empty');
                return;
            } else {
                watchAction(index, i + 1);
            }
        }, 1000);
        return;
    }
    console.log(['watchAction: ', index])

    buildPlayers();

    useCommentSectionStore().item = shorts.value[index];
    useCommentSectionStore().item_type = shorts.value[index].type;

    // grab interactions first then comments
    useCommentSectionStore().getCommentInteractions().then(() => {
        useCommentSectionStore().fetchComments("order by");
    });
}

function createVisibleIndices() {

    let index = fullyVisibleIndex.value;
    let visibleIndices = [index];
    if (index === 0) {
        // if at start
        visibleIndices = [index, index + 1, index + 2, index + 3, index + 4];
    } else if (index >= shorts.value.length - 1) {
        // if at end
        visibleIndices = [index - 2, index - 1, index , index + 1];
    } else {
        // if in middle
        visibleIndices =  [index - 1, index, index + 1, index + 2];
    }

    // check visible indices are within bounds of shorts
    // if return what ever is within bounds
    visibleIndices = visibleIndices.filter(index => index >= 0 && index < shorts.value.length);

    // get external ids of shorts that should be visible
    console.log(['These shorts should be loaded',  visibleIndices.map(index => shorts.value[index].external_id)]);
    return visibleIndices;

}

function buildPlayers() {
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

        // if visible player doesn't exist, build it and play it
        let id = 'player_div_holder_' + shorts.value[visibleIndex].external_id; // external_id is the ref to the div
        let object = shorts.value[visibleIndex];

        if (!usePlayerStore().findPlayer(shorts.value[visibleIndex].external_id)) {
            console.log('BUILDING PLAYER ' + shorts.value[visibleIndex].external_id);
            usePlayerStore().buildPlayer(id, object, 0, false, false, true).then(() => {
                // playFullyVisiblePlayer(visibleIndex);
            });
        }
    }

    //loop through the shorts and check if the player is visible
    // for (let i = 0; i < shorts.value.length; i++) {
    //     if (!visibleIndices.includes(i)) {
    //         // if not the visible players, destroy it
    //         let player = usePlayerStore().findPlayer(shorts.value[i].external_id)
    //
    //         if (player) {
    //             usePlayerStore().destroyPlayer(shorts.value[i].external_id, false, true);
    //         }
    //     }
    // }


};

function playFullyVisiblePlayer(i) {
    if (i === fullyVisibleIndex.value) {
        console.log('PLAYING VISIBLE PLAYER ' + shorts.value[fullyVisibleIndex.value].external_id);
        let player;
        player = usePlayerStore().findPlayer(shorts.value[fullyVisibleIndex.value].external_id);
        if (player) {
            // player.togglePlay();
        } else {
            console.log('player not found');
        }
    }
}

onUnmounted(() => {
    // destroy all players
    usePlayerStore().destroyPlayers(true, true);
});


</script>
<!--rerender everytime page reloads-->
<template >
    <div>
        <Head title="VidGaze Shorts" />

        <div v-bind="containerProps" class="max-h-[calc(100vh-4rem)] duration-75  overflow-y-scroll snap snap-y snap-mandatory ease-in-out" v>
            <div v-bind="wrapperProps">
                <div id="shortsScrollArea" class=" w-full ">
                    <template v-if="list.length > 0" v-for="{index, data} in list" :key="index" >
                        <ShortsPlayer :video="data" :index="index" v-if="data !== undefined" @UpdateFullyVisibleIndex="UpdateFullyVisibleIndex(index)" :key="index"/>
                    </template>
                    <template v-else>
                        <ShortsPlayerSkeleton />
                    </template>
                </div>
            </div>
        </div>

    </div>
</template>
