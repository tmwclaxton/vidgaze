
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
import {onMounted, ref, toRaw, watch} from "vue";
import { useInfiniteScroll, useVirtualList, useIntersectionObserver } from '@vueuse/core';

import {usePlayerStore} from "@/Stores/PlayerStore";
import {debounce} from "lodash";
const playerStore = usePlayerStore();

const name = 'Shorts'
const shorts = ref([]);
// this is the index of the short that is fully visible
const fullyVisibleIndex = ref(0);

const { list, containerProps, wrapperProps } = useVirtualList(shorts, {
    itemHeight: window.innerHeight - 64,
    itemWidth: 400,
});


const category = ref('popular');
const fetchShorts = async () => {
    let shortsIds = [];
    if (shorts.value.length > 0) {
        // const shortsIds = shorts.value.map(short => short.id).join(','); // what if there are no shorts?
        shortsIds = shorts.value.map(short => short.id).join(',');
    } else {
        shortsIds = [];
    }
    axios.get(route('videos.infinite'),  {
        params: {
            category: category.value,
            shorts: true,
            perPage: 8,
            videoIds: shortsIds
        }
    }).then(response => {
            if (response.data.data === undefined || response.data.data.length === 0) {
                return;
            }
            console.log("FETCHING SHORTS");
            shorts.value = shorts.value.concat(response.data.data);
        })
        .catch(error => {
            console.log(error);
        });
};

useInfiniteScroll(
    containerProps.ref,
    async () => {
        await fetchShorts();
    },
    {
        distance: 3 * (window.innerHeight - 64), // load more when scrolled to within 2 shorts from the bottom
    }
)



const UpdateFullyVisibleIndex = (index) => {
    fullyVisibleIndex.value = index;
};

watch(fullyVisibleIndex, (index) => {
    console.log(['current short: ', index])
    buildPlayers();

});

function createVisibleIndices() {

    let index = fullyVisibleIndex.value;
    let visibleIndices = [];
    if (index < 1) {
        // if at start
        visibleIndices = [index, index + 1, index + 2, index + 3, index + 4];
    } else if (index >= shorts.value.length - 1) {
        // if at end
        visibleIndices = [index - 2, index - 1, index , index + 1];
    } else {
        // if in middle
        visibleIndices =  [index - 1, index, index + 1, index + 2];
    }
    // get external ids of shorts that should be visible
    console.log(['These shorts should be loaded',  visibleIndices.map(index => shorts.value[index].external_id)]);
    return visibleIndices;
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

function playFullyVisiblePlayer(i) {
    //
    console.log(i + ' comapared to ' + fullyVisibleIndex.value)
    if (i === fullyVisibleIndex.value) {
        console.log('PLAYING VISIBLE PLAYER ' + shorts.value[fullyVisibleIndex.value].external_id);
        // console.log('playing player ' + shorts.value[fullyVisibleIndex.value].external_id);

        // check every 1 second fi player has been built in the playerStore once it has been built, play it and stop checking

        if (playerStore.findPlayer(shorts.value[fullyVisibleIndex.value].external_id)) {
            playerStore.play(shorts.value[fullyVisibleIndex.value].external_id);
        } else {
            console.log('player not built yet');
            setTimeout(() => {
                playFullyVisiblePlayer(i);
            }, 2000);
        }
    }
}

onMounted(async () => {
    shorts.value = [];
    fullyVisibleIndex.value = 0;
    playerStore.destroyPlayers();

    await fetchShorts().then(() => {
        // watch shorts for changes // debounce the function so it only runs once every 100s
        watch(shorts, (shorts) => debounce(() => {
            // console.log(shorts);
            // build the first 3 players as soon as shorts is not empty, wait until shorts is not empty
            if (shorts.length > 0) {
                buildPlayers(0);
            }
        } , 100)());

    });
});

</script>
<!--rerender everytime page reloads-->
<template >
    <Head title="VidGaze Shorts" />

    <div v-bind="containerProps" class="max-h-[calc(100vh-4rem)] duration-75  overflow-y-scroll snap snap-y snap-mandatory ease-in-out" v>
        <div v-bind="wrapperProps">
            <div id="shortsScrollArea" class=" w-full ">
                <template v-if="list.length > 0" v-for="{index, data} in list" :key="index">
                    <ShortsPlayer :video="data" :index="index" v-if="data !== undefined" @UpdateFullyVisibleIndex="UpdateFullyVisibleIndex(index)"/>
                </template>
                <template v-else>
                    <ShortsPlayerSkeleton />
                </template>
            </div>
        </div>
    </div>
</template>
