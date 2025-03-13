<script setup>

import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {onMounted, ref, watch} from "vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import VideoStreamSuggestionCard
    from "@/Components/Cards/VideoStreamCards/VideoStreamSuggestionCard/VideoStreamSuggestionCard.vue";
import {shuffle} from "lodash";
import VideoStreamSuggestionSkeleton
    from "@/Components/Cards/VideoStreamCards/VideoStreamSuggestionCard/VideoStreamSuggestionSkeleton.vue";

const name = 'SuggestedVideos';

const props = defineProps({
    video: {
        type: Object,
        required: true
    },
    creator: {
        type: Object,
        required: false
    }
});

const suggestions = ref([]);
const mode = ref("category");
const page = ref(null);

onMounted(async () => {
    await loadMore();
});


const loadMore = async () => {
    if (mode.value === "category" || props.creator === null) {
        const videoIds = suggestions.value.map(video => video.id).join(',');
        const extraItems = await useContentRoutesStore().getCategoryVideos(props.video.category.slug, 10, videoIds);
        suggestions.value = suggestions.value.concat(extraItems);
    } else if (mode.value === "channel") {
        console.log(props.creator);
        const result = await useContentRoutesStore().getChannelVideos(props.creator, 30, page.value);
        if (result.videos.length === 0) {
            return;
        }

        // treat videos as a set, so no duplicates
        const videoIds = suggestions.value.map(video => video.id);
        result.videos = result.videos.filter(video => !videoIds.includes(video.id));
        suggestions.value = shuffle([...suggestions.value, ...result.videos]);
        page.value = result.nextPage;
    }
};

//watch for changes in mode if so delete suggestions and load new ones
watch(mode, () => {
    suggestions.value = [];
    loadMore();
});

</script>

<template>
    <div class="">
        <div class="flex flex-row flex-wrap">
            <QuaternaryButton v-if="video.category !== undefined" class="mr-2" @click="mode = 'category'">
                <font-awesome-icon :icon="['fas', 'icons']" class="my-auto"/>
                <span class="font-semibold" v-text="video.category.name"/>
            </QuaternaryButton>
            <QuaternaryButton class="mr-2" @click="mode = 'channel'">
                <font-awesome-icon :icon="['fas', 'heart']" class="my-auto"/>
                <span class="font-semibold">Channel</span>
            </QuaternaryButton>
        </div>

        <div >
            <div class="relative flex flex-col pb-1 overflow-y-auto gap-y-2 mt-3 overflow-hidden" >
                <VideoStreamSuggestionCard v-if="suggestions && suggestions.length > 0" v-for="(item, index) in suggestions" :item="item" :key="index"/>
                <VideoStreamSuggestionSkeleton v-else v-for="i in 10" :key="i"/>
            </div>
        </div>
        <div v-if="suggestions && suggestions.length > 0" @click="loadMore">
            <QuaternaryButton @click="loadMore" class="font-bold rounded-sm text-sm py-2.5 mb-2 w-full shine px-0">
                <p  class="text-center ">Load More Videos</p>
            </QuaternaryButton>
        </div>
    </div>
</template>


