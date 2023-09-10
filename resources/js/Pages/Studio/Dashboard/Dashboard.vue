<script setup>
import { Head } from '@inertiajs/vue3';
import Title from "@/Components/General/TitleComponent.vue";

import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";

import ConnectChannels from "@/Pages/Studio/Dashboard/Partials/ConnectChannels.vue";
import ItemPerformance from "@/Pages/Studio/Dashboard/Partials/ItemPerformance.vue";
import LatestComments from "@/Pages/Studio/Dashboard/Partials/LatestComments.vue";
import ChannelOverview from "@/Pages/Studio/Dashboard/Partials/ChannelOverview.vue";
import {onMounted, ref} from "vue";
import {useCommentSectionStore} from "@/Stores/CommentSectionStore";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";

const comments = ref(null);
const item = ref(null);
const item_analytics = ref(null);
onMounted(() => {
    axios.get(route('api.studio.comments'))
        .then(response => {
            comments.value = response.data.comments;
        })
        .catch(error => {
            console.log(error);
        });
    axios.get(route('api.studio.latest.video'))
        .then(response => {
            item.value = response.data.video;
            item_analytics.value = response.data.analytic;
        })
        .catch(error => {
            console.log(error);
        });
});

</script>
<template>
    <Head title="Studio Dashboard" />

    <ConsistentPadding class="-mt-4">
        <Title :text="'Channel Dashboard'" class="my-4 mb-8">
            <font-awesome-icon :icon="['fas', 'house']" class="w-6 h-6 my-auto"/>
        </Title>

        <div class="flex flex-col sm:grid sm:grid-cols-6 sm:grid-rows-4 gap-4 -mt-2">
            <!--channel overview -->
            <div class="col-span-2 row-span-2">
                <ChannelOverview/>
            </div>

            <!--connect channels-->
            <div class="col-span-4 row-span-1">
                <ConsistentContentHolder class="p-5 h-full">
                    <ConnectChannels/>
                </ConsistentContentHolder>
            </div>


            <!--latest item performance-->
            <div v-if="item != null"
                class="col-span-4 row-span-2">
                <ItemPerformance :item="item" :item_analytics="item_analytics"/>
            </div>

            <div v-if="comments != null && comments.length > 0"
                class="col-span-2 row-span-2">
                <LatestComments :comments="comments"/>
            </div>
        </div>

    </ConsistentPadding>
</template>
