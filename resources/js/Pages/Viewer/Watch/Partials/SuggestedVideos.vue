<script setup>

import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {computed, onMounted, ref, watch} from "vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import VideoStreamSuggestionCard
    from "@/Components/Cards/VideoStreamCards/VideoStreamSuggestionCard/VideoStreamSuggestionCard.vue";
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

const store = useContentRoutesStore();
const suggestions = ref([]);
const mode = ref("related");
const page = ref(null);
const loading = ref(false);
const exhausted = ref(false);

const categorySlug = computed(() => props.video?.category?.slug ?? null);

const addUnique = (items) => {
    const seen = new Set(suggestions.value.map(v => v.id));
    seen.add(props.video.id);
    suggestions.value = suggestions.value.concat((items ?? []).filter(v => v && !seen.has(v.id)));
};

const loadMore = async () => {
    if (loading.value || exhausted.value) return;
    loading.value = true;
    const before = suggestions.value.length;
    try {
        if (mode.value === "related") {
            if (before === 0) {
                addUnique(await store.getRelatedVideos(props.video.id, 20));
            } else if (categorySlug.value) {
                const ids = suggestions.value.map(v => v.id).concat([props.video.id]).join(',');
                addUnique(await store.getCategoryVideos(categorySlug.value, 10, ids));
            }
        } else if (mode.value === "category" && categorySlug.value) {
            const ids = suggestions.value.map(v => v.id).concat([props.video.id]).join(',');
            addUnique(await store.getCategoryVideos(categorySlug.value, 10, ids));
        } else if (mode.value === "channel" && props.creator) {
            const result = await store.getChannelVideos(props.creator, 30, page.value);
            addUnique(result.videos);
            page.value = result.nextPage;
            if (!result.nextPage) exhausted.value = true;
        }
    } finally {
        if (suggestions.value.length === before) exhausted.value = true;
        loading.value = false;
    }
};

onMounted(loadMore);

// switching tabs, or moving to another video, starts a fresh list
const reset = () => {
    suggestions.value = [];
    page.value = null;
    exhausted.value = false;
    loadMore();
};
watch(mode, reset);
watch(() => props.video?.id, reset);

</script>

<template>
    <div class="">
        <div class="flex flex-row flex-wrap gap-2" role="tablist" aria-label="Suggestions">
            <QuaternaryButton role="tab" :aria-selected="mode === 'related'"
                              :class="mode === 'related' ? 'ring-2 ring-white/70' : ''" @click="mode = 'related'">
                <font-awesome-icon :icon="['fas', 'play']" class="my-auto"/>
                <span class="font-semibold">Up next</span>
            </QuaternaryButton>
            <QuaternaryButton v-if="categorySlug" role="tab" :aria-selected="mode === 'category'"
                              :class="mode === 'category' ? 'ring-2 ring-white/70' : ''" @click="mode = 'category'">
                <font-awesome-icon :icon="['fas', 'icons']" class="my-auto"/>
                <span class="font-semibold" v-text="video.category.name"/>
            </QuaternaryButton>
            <QuaternaryButton v-if="creator" role="tab" :aria-selected="mode === 'channel'"
                              :class="mode === 'channel' ? 'ring-2 ring-white/70' : ''" @click="mode = 'channel'">
                <font-awesome-icon :icon="['fas', 'heart']" class="my-auto"/>
                <span class="font-semibold">Channel</span>
            </QuaternaryButton>
        </div>

        <div>
            <div class="relative flex flex-col pb-1 overflow-y-auto gap-y-2 mt-3 overflow-hidden">
                <VideoStreamSuggestionCard v-if="suggestions.length > 0" v-for="item in suggestions" :item="item" :key="item.id"/>
                <VideoStreamSuggestionSkeleton v-else-if="loading" v-for="i in 10" :key="'s' + i"/>
                <p v-else class="text-sm text-zinc-400 py-4">No suggestions yet.</p>
            </div>
        </div>
        <div v-if="suggestions.length > 0 && !exhausted">
            <QuaternaryButton @click="loadMore" class="font-bold rounded-sm text-sm py-2.5 mb-2 w-full shine px-0">
                <p class="text-center">{{ loading ? 'Loading...' : 'Load More Videos' }}</p>
            </QuaternaryButton>
        </div>
    </div>
</template>
