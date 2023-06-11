
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
            perPage: 6,
            videoIds: shortsIds
        }
    }).then(response => {
            if (response.data.data === undefined || response.data.data.length === 0) {
                return;
            }
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
        distance: 2 * (window.innerHeight - 64), // load more when scrolled to within 2 shorts from the bottom
    }
)



const UpdateFullyVisibleIndex = (index) => {
    // console.log(index)
    fullyVisibleIndex.value = index;
};

watch(fullyVisibleIndex, (index) => {


    console.log(index)
    buildPlayers(index);

});

function createVisibleIndices(index) {
    let visibleIndices = [];
    if (index === 0) {
        visibleIndices = [index, index + 1, index + 2];
    } else if (index === shorts.value.length - 1) {
        visibleIndices = [index - 2, index - 1, index];
    } else {
        visibleIndices = [index - 1, index, index + 1];
    }
    // console.log(index)
    console.log(visibleIndices);
    return visibleIndices;
}

function buildPlayers(index) {
    // make a list of the indices of the shorts that should be visible
    // 3 short players should be visible at a time
    // we should have an index of the current short player
    // we figure what players should be visible based on the index
    // if the index is 0, we show the first 3 players, if the index is 1, we show the zeroth, first and second players


    let visibleIndices = createVisibleIndices(index);


    // loop through the 3 players that should be built and check if player has been loaded
    // if not, load it using playerStore

    let builtPlayers = [];
    for (let i = 0; i < visibleIndices.length; i++) {
        const visibleIndex = visibleIndices[i];
        // load player if shorts is undefined
        if (shorts.value[visibleIndex] === undefined) {
            console.log('shorts is undefined');
            return;
        }
        let player = playerStore.findPlayer(shorts.value[visibleIndex].external_id);
        if (!player) {
            // console.log('building player ' + shorts.value[visibleIndex].external_id);

            builtPlayers.push(shorts.value[visibleIndex].external_id);

            let id = 'player_div_holder_' + shorts.value[visibleIndex].external_id; // external_id is the ref to the div
            let object = shorts.value[visibleIndex];
            let autoplay = false;
            let start = 0;
            playerStore.buildPlayer(id, object, start, autoplay);
        } else {
            // console.log('player already exists ' + shorts.value[visibleIndex].external_id);

        }
    }

    console.log(builtPlayers);


    //loop through the shorts and check if the player is visible
    for (let i = 0; i < shorts.value.length; i++) {
        if (visibleIndices.includes(i)) {
            // if the player is visible, play it | we don't need to pause the other players because PlayerStore does that for us
            if (i === fullyVisibleIndex.value) {
                console.log('playing player ' + shorts.value[i].external_id);
                playerStore.play(shorts.value[i].external_id);
            }
        } else {
            // if not  the 3 players , destroy the rest

            //destroy all players aside from the 3 that should be visible
            let player = playerStore.findPlayer(shorts.value[i].external_id)

            if (player) {
                console.log('destroying player ' + shorts.value[i].external_id);
                playerStore.destroyItem(player);
            } else {
                // console.log('player does not exist ' + shorts.value[i].external_id);
            }


        }


    }





};


onMounted(async () => {
    await fetchShorts().then(() => {
        // watch shorts for changes
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

<template>
    <Head title="VidGaze Shorts" />

    <div v-bind="containerProps" class="max-h-[calc(100vh-4rem)] duration-75  overflow-y-scroll snap snap-y snap-mandatory ease-in-out">
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
