
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
import { onMounted, ref, watch } from "vue";
import { useInfiniteScroll, useVirtualList, useIntersectionObserver } from '@vueuse/core';

import {usePlayerStore} from "@/Stores/PlayerStore";
const playerStore = usePlayerStore();

const name = 'Shorts'
const shorts = ref([]);

const { list, containerProps, wrapperProps } = useVirtualList(shorts, {
    itemHeight: window.innerHeight - 64,
    itemWidth: 400,
});


const category = ref('popular');
const fetchShorts = async () => {
    // const shortsIds = shorts.value.map(short => short.id).join(','); // what if there are no shorts?
    const shortsIds = shorts.value.length > 0 ? shorts.value.map(short => short.id).join(',') : [];
    axios.get(route('videos.infinite'),  {
        params: {
            category: category.value,
            shorts: true,
            perPage: 8,
            videoIds: shortsIds
        }
    }).then(response => {
            setTimeout(() => {
                if (response.data.data === undefined || response.data.data.length === 0) {
                    return;
                }
                shorts.value = shorts.value.concat(response.data.data);
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};

onMounted(async () => {
    await fetchShorts();
});

useInfiniteScroll(
    containerProps.ref,
    async () => {
        await fetchShorts();
    },
    {
        distance: 2 * (window.innerHeight - 64), // load more when scrolled to within 2 shorts from the bottom
    }
)



const fullyVisibleIndex = ref(0);

const UpdateFullyVisibleIndex = (index) => {
    // console.log(index)
    fullyVisibleIndex.value = index;
};

watch(fullyVisibleIndex, (index) => {
    console.log(index)
    // make a list of the indices of the shorts that should be visible
    // 3 short players should be visible at a time
    // we should have an index of the current short player
    // we figure what players should be visible based on the index
    // if the index is 0, we show the first 3 players, if the index is 1, we show the zeroth, first and second players

    let visibleIndices = [];
    if (index === 0) {
        visibleIndices = [index, index + 1, index + 2];
    } else if (index === shorts.value.length - 1) {
        visibleIndices = [index - 2, index - 1, index];
    } else {
        visibleIndices = [index - 1, index, index + 1];
    }
    console.log(visibleIndices);

    // loop through visibile indices and check if player has been loaded
    // if not, load it using playerStore

    for (let i = 0; i < visibleIndices.length; i++) {
        const visibleIndex = visibleIndices[i];
        if (shorts.value[visibleIndex].player === undefined) {
            // load player
            let player = playerStore.findPlayer(shorts.value[visibleIndex].external_id);
            if (!player) {
                console.log('building player');
                playerStore.autoplay = false;
                playerStore.object = shorts.value[visibleIndex];

                playerStore.buildPlayer(shorts.value[visibleIndex].external_id); // external_id is the ref to the div
            } else {
                console.log('player already exists');
            }
        }

        // setTimeout(() => {
        //     // if current index play video
        //     if (visibleIndex === i) {
        //         console.log('playing video');
        //         playerStore.play(shorts.value[visibleIndex].external_id);
        //     } else {
        //         console.log('pausing video');
        //         // playerStore.pause(shorts.value[visibleIndex].external_id);
        //     }
        // }, 500 );


    }


});
</script>

<template>
    <Head title="VidGaze Shorts" />
    <div v-bind="containerProps" class="max-h-[calc(100vh-4rem)] duration-75  overflow-y-scroll snap snap-y snap-mandatory ease-in-out">
        <div v-bind="wrapperProps">
            <div id="shortsScrollArea" class=" w-full ">
                <template v-if="list.length > 0" v-for="{index, data} in list" :key="index">
                    <ShortsPlayer :video="data" v-if="data !== undefined" @UpdateFullyVisibleIndex="UpdateFullyVisibleIndex(index)"/>
                </template>
            </div>
        </div>
        <ShortsPlayerSkeleton  v-for="n in 1"  />
    </div>
</template>
