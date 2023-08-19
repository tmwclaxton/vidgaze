
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

export default {
    layout: AuthenticatedLayout
};
</script>

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
const playerStore = usePlayerStore();
const commentSectionStore = useCommentSectionStore();
const name = 'Shorts'
const shorts = ref([]);
// this is the index of the short that is fully visible
const fullyVisibleIndex = ref(0);
const contentRoutesStore = useContentRoutesStore();

const { list, containerProps, wrapperProps } = useVirtualList(shorts, {
    itemHeight: window.innerHeight - 64,
    itemWidth: 400,
});


const category = ref('popular');
const fetchShorts = async (first_video_slug = null) => {
    let shortsIds = [];
    if (shorts.value.length > 0) {
        // const shortsIds = shorts.value.map(short => short.id).join(','); // what if there are no shorts?
        shortsIds = shorts.value.map(short => short.id).join(',');
    } else {
        shortsIds = [];
    }
    await contentRoutesStore.getVideos(category.value, 8,shortsIds, true, first_video_slug)
        .then(response => {
            console.log("FETCHING SHORTS");
            if (response === undefined || response.length === 0) {
                console.log('no more shorts');
                return;
            }
            shorts.value = shorts.value.concat(response)
            console.log(shorts.value);
        })
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

const UpdateFullyVisibleIndex = (index) => {
    fullyVisibleIndex.value = index;
};

watch(fullyVisibleIndex, (index) => {
    if (shorts.value.length === 0) {
        return;
    }
    console.log(['current short: ', index])
    buildPlayers();

    commentSectionStore.item = shorts.value[index];
    commentSectionStore.item_type = shorts.value[index].type;

    // grab interactions first then comments
    commentSectionStore.getCommentInteractions();
    setTimeout(() => {
        commentSectionStore.fetchComments(category.value);
    }, 200); // 200ms delay

});

onMounted(async () => {

    setTimeout(async () => {
        shorts.value = [];
        fullyVisibleIndex.value = 0;
        await playerStore.destroyPlayers(); // destroy all players
        console.log('shorts index mounted');

        // if short slug is in url, play that short
        const urlParams = new URLSearchParams(window.location.search);
        const firstShort = urlParams.get('short') || null;

        if ( shorts.value.length === 0 ) {
            await fetchShorts(firstShort).then(() => {
                // if short slug is in url, play that short
                    playFullyVisiblePlayer(0);
            });
        }
    }, 1000); // 1s delay



});


function createVisibleIndices() {

    // let index = fullyVisibleIndex.value;
    // let visibleIndices = [index];
    // if (index === 0) {
    //     // if at start
    //     visibleIndices = [index, index + 1, index + 2, index + 3, index + 4];
    // } else if (index >= shorts.value.length - 1) {
    //     // if at end
    //     visibleIndices = [index - 2, index - 1, index , index + 1];
    // } else {
    //     // if in middle
    //     visibleIndices =  [index - 1, index, index + 1, index + 2];
    // }
    //
    // // check visible indices are within bounds of shorts
    // // if return what ever is within bounds
    // visibleIndices = visibleIndices.filter(index => index >= 0 && index < shorts.value.length);
    //
    // // get external ids of shorts that should be visible
    // console.log(['These shorts should be loaded',  visibleIndices.map(index => shorts.value[index].external_id)]);

    return shorts.value.map((short, index) => index);
}

function buildPlayers() {
    console.log('BUILDING PLAYERS -----------------------');
    // make a list of the indices of the shorts that should be visible
    // 3 short players should be visible at a time
    // we should have an index of the current short player
    // we figure what players should be visible based on the index
    // if the index is 0, we show the first 3 players, if the index is 1, we show the zeroth, first and second players


    let visibleIndices = createVisibleIndices();


    // loop through the 3 players that should be built and check if player has been loaded
    // if not, load it using playerStore

    let builtPlayers = []; // for testing

    for (let i = 0; i < visibleIndices.length; i++) {
        let visibleIndex = visibleIndices[i];
        // load player if shorts is undefined
        if (shorts.value[visibleIndex] === undefined) {
            console.log('shorts is undefined');
            return;
        }
        let player = playerStore.findPlayer(shorts.value[visibleIndex].external_id);

        // if visible player doens't exists, build it and play it | if it exists, play it
        if (!player) {
            builtPlayers.push(shorts.value[visibleIndex].external_id); // for testing

            let id = 'player_div_holder_' + shorts.value[visibleIndex].external_id; // external_id is the ref to the div
            let object = shorts.value[visibleIndex];

            playerStore.buildPlayer(id, object, 0, false).then(() => {
                // if the player is visible, play it | we don't need to pause the other players because PlayerStore does that for us
                playFullyVisiblePlayer(visibleIndex);
            });
        } else {
            // if it is visible, play it
            playFullyVisiblePlayer(visibleIndex);
        }

    }

    console.log(['Built these players',builtPlayers]);


    let removePlayers = []; // for testing

    //loop through the shorts and check if the player is visible
    for (let i = 0; i < shorts.value.length; i++) {
        if (!visibleIndices.includes(i)) {
            // if not the visible players, destroy it
            let player = playerStore.findPlayer(shorts.value[i].external_id)

            if (player) {
               removePlayers.push(shorts.value[i].external_id); // for testing
                playerStore.destroyItem(player);
            } else {
                // console.log('player does not exist ' + shorts.value[i].external_id);
            }
        }
    }

    console.log(['Removed these players',removePlayers]);



};

function playFullyVisiblePlayer(i, count = 0) {
    if (i === fullyVisibleIndex.value) {
        console.log('PLAYING VISIBLE PLAYER ' + shorts.value[fullyVisibleIndex.value].external_id);

        // check every 1 second fi player has been built in the playerStore once it has been built, play it and stop checking

        if (playerStore.findPlayer(shorts.value[fullyVisibleIndex.value].external_id)) {
            playerStore.play(shorts.value[fullyVisibleIndex.value].external_id);
        } else {
            console.log('player not built yet');
            setTimeout(() => {

                if (count % 3 === 0) {
                    console.log("rebuild attempt")
                     // build player if it hasn't been built after 10 seconds
                    playerStore.buildPlayer('player_div_holder_' + shorts.value[fullyVisibleIndex.value].external_id, shorts.value[fullyVisibleIndex.value], 0, false).then(() => {
                        playerStore.play(shorts.value[fullyVisibleIndex.value].external_id);
                    });
                }
                playFullyVisiblePlayer(i, count + 1);
            }, 2000);
        }
    }
}

onUnmounted(() => {
    // destroy all players
    playerStore.destroyPlayers();
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
