

<script setup>
import {onMounted, onUnmounted, ref} from "vue";
import CreatorCarousel from "@/Pages/Viewer/Home/CreatorCarousel.vue";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import {debounce} from "lodash";

import TopStreamsRow from "@/Components/ContentRows/TopStreamsRow.vue";
import TopShortsRow from "@/Components/ContentRows/TopShortsRow.vue";

import VideosRow from "@/Components/ContentRows/VideosRow.vue";
import InfiniteVideos from "@/Components/ContentRows/InfiniteVideos.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import {useAuthStore} from "@/Stores/AuthStore";
import {usePinModalStore} from "@/Stores/PinModalStore";
import RumbleIcon from '~/images/icons/rumble.svg';
import VimeoIcon from '~/images/icons/vimeo.svg';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const pinModalStore = usePinModalStore();
const contentRoutesStore = useContentRoutesStore();


const pinnedVideos = ref([]);



onMounted(async () => {
    await pinModalStore.getVideoCategories();
    // // iterate over the categories and get the pinned videos for each
    for (const category of pinModalStore.categories.data) {
        const videos = await pinModalStore.getPinnedVideos(6, 1, category.slug);
        pinnedVideos.value.push({
            category: category,
            videos: videos
        });
    }

    // order pinned videos by most videos
    pinnedVideos.value.sort((a, b) => {
        return b.videos.length - a.videos.length;
    });




    // vimeoPinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'Vimeo');
    // rumblePinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'Rumble');
    // musicPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.music);
    // techPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.tech);
    // wealthPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.wealth);
});




</script>
<template>
    <div>
        <Head title="Categories" />

        <ConsistentPadding class="-mt-4">




            <VideosRow v-for="item in pinnedVideos" :videos="item.videos" :key="item.category.id" :title="item.category.name">
            </VideosRow>



        </ConsistentPadding>
    </div>
</template>
