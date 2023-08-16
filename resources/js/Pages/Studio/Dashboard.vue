<script setup>
import { Head } from '@inertiajs/vue3';
import Title from "@/Components/General/Title.vue";

import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";

import ConnectChannels from "@/Pages/Studio/Partials/ConnectChannels.vue";
import ItemPerformance from "@/Pages/Studio/Partials/ItemPerformance.vue";
import LatestComment from "@/Pages/Studio/Partials/LatestComment.vue";
import ChannelOverview from "@/Pages/Studio/Partials/ChannelOverview.vue";
import StreamIcon from '~/images/icons/livestreams.svg';

import {defineProps} from "vue";
import {onMounted, ref} from "vue";


let claimedPlatforms = ref({});

onMounted(async () => {
    claimedPlatforms.value = await axios.get(route("api.my.creator.sources")).then((response) => {
        return response.data.sources;
    });
});


</script>
<template>
    <Head title="Studio Dashboard" />

    <ConsistentPadding class="-mt-4">
        <Title :text="'Channel Dashboard'">
            <StreamIcon class="w-6 h-6 my-auto"/>
        </Title>

        <div class="grid grid-cols-6 grid-rows-6 gap-4 -mt-2">
            <!--channel overview -->
            <div class="col-span-2 row-span-2">
                <ChannelOverview/>
            </div>

            <!--connect channels-->
            <div class="col-span-4 row-span-1">
                <ConsistentContentHolder class="p-5 h-full">
                    <ConnectChannels :claimed_platforms="claimedPlatforms"/>
                </ConsistentContentHolder>
            </div>


            <!--latest item performance-->
            <div class="col-span-4 row-span-2">
                <ItemPerformance/>
            </div>

            <div class="col-span-2 row-span-2">
                <LatestComment/>
            </div>
        </div>

    </ConsistentPadding>
</template>
