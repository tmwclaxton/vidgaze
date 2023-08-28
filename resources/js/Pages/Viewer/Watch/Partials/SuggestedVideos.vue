<script setup>

import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {onMounted, ref, watch} from "vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import VideoStreamSuggestionCard
    from "@/Components/Cards/VideoStreamCards/VideoStreamSuggestionCard/VideoStreamSuggestionCard.vue";

const name = 'SuggestedVideos';

const props = {
    video: {
        type: Object,
        required: true
    }
};

const suggestions = ref([]);
const mode = ref("recommended");

onMounted(async () => {
    await loadMore();
});

const loadMore = async () => {
    if (mode.value === "recommended") {
        const videoIds = suggestions.value.map(video => video.id).join(',');
        const extraItems = await useContentRoutesStore().getVideos("random", 10, videoIds);
        suggestions.value = suggestions.value.concat(extraItems);
    } else if (mode.value === "channel") {
        // not implemented yet

    }
};

//watch for changes in mode if so delete suggestions and load new ones
watch(mode, () => {
    suggestions.value = [];
    loadMore();
});

</script>

<template>
    <div class="flex flex-row flex-wrap">
        <QuaternaryButton class="mr-2" @click="mode = 'recommended'">
            <font-awesome-icon :icon="['fas', 'fire']" class="my-auto"/>
            <span class="font-semibold">Random</span>
        </QuaternaryButton>
        <QuaternaryButton class="mr-2" @click="mode = 'channel'">
            <font-awesome-icon :icon="['fas', 'heart']" class="my-auto"/>
            <span class="font-semibold">Channel</span>
        </QuaternaryButton>
    </div>

    <div v-if="suggestions && suggestions.length > 0">
        <div class="relative flex flex-col pb-1 overflow-y-auto gap-y-2" >
            <VideoStreamSuggestionCard v-for="(item, index) in suggestions" :item="item" :key="index"/>
        </div>
    </div>

    <div v-if="suggestions && suggestions.length > 0" @click="loadMore" class="right-aligned font-bold focus:ring-2 focus:outline-none  rounded-sm text-sm
                                py-2.5  mb-2 uppercase w-full generic_button_2 dark:generic_button-dark_2  shine px-0">
        <p  class="text-center select-none">Load More Videos</p>
    </div>
</template>


