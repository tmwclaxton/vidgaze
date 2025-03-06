

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

const trending_videos = ref([]);
const videos = ref([]);
const shorts = ref([]);
const category = ref('');

const vimeoPinned = ref([]);
const rumblePinned = ref([]);
const musicPinned = ref([]);
const techPinned = ref([]);
const wealthPinned = ref([]);

const categorySlugs = {
    'music': 'music',
    'tech': 'freedom_technology',
    'wealth': 'wealth_inequality',
};



onMounted(async () => {
    await fetchTrendingVideos().then(async () => {
        await debouncedFetchVideos(); // call it immediately on mount

        window.addEventListener('scroll', handleScroll);
    });

    vimeoPinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'Vimeo');
    rumblePinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'Rumble');
    musicPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.music);
    techPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.tech);
    wealthPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.wealth);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    debouncedFetchVideos.cancel(); // cancel any pending debounced calls
});

const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;
    // console.log(scrollPosition, bodyHeight)

    // check if user has reached the bottom of the page
    if (scrollPosition >= bodyHeight - 100) {
        // console.log('bottom of page')
        debouncedFetchVideos(); // call the debounced version of fetchVideos
    }
};

// debounced version of fetchVideos that waits for 500ms before calling
const debouncedFetchVideos = debounce(() => {
    if (trending_videos.value) {
        fetchVideos([...trending_videos.value, ...videos.value]);
    } else {
        fetchVideos([...videos.value]);
    }
}, 500);

const fetchTrendingVideos = async () => {
    trending_videos.value = [];
    await contentRoutesStore.getVideos('random', 6)
        .then(response => {
            trending_videos.value = response
        }).catch(error => {
            console.log(error)
        });

};
const fetchVideos = async (videoArray) => {

    const videoIds = videoArray.map(video => video.id).join(',');
    const response = await contentRoutesStore.getVideos('trending', 40, videoIds)

    if (response === undefined) {
        window.removeEventListener('scroll', handleScroll);
    } else {
        videos.value = videos.value.concat(response);
    }

};




</script>
<template>
    <div>
        <Head title="Home" />

            <!--show 12 videos, hide videos if they don't fill the row completely-->
            <div class="flex-grow hidden md:flex">
                <CreatorCarousel />
            </div>
<!--            <p class="px-16 mx-auto text-lg text-center w-full font-bold">-->
<!--                "Just remember to ascribe fully to an ideology is to give up your autonomy"-->
<!--            </p>-->

        <ConsistentPadding class="-mt-4">

<!--            <VideosRow :videos="trending_videos" title="Trending Videos">-->
<!--                <font-awesome-icon :icon="['fas', 'burst']" class="my-auto h-6"/>-->
<!--            </VideosRow>-->

            <!--<TopStreamsRow/>-->



            <VideosRow :videos="vimeoPinned" title="Vimeo Spotlight">
                <VimeoIcon class="my-auto h-6"/>
            </VideosRow>

            <VideosRow :videos="rumblePinned" title="Hot on Rumble">
                <RumbleIcon class="my-auto h-6"/>
            </VideosRow>

            <VideosRow :videos="wealthPinned" title="Wealth Inequality">
                <img src="/images/monoply.png" class="my-auto h-12 -mt-1"/>
            </VideosRow>

            <VideosRow :videos="techPinned" title="Freedom Technology">
                <font-awesome-icon :icon="['fas', 'microchip']" class="my-auto h-6"/>
            </VideosRow>

            <TopShortsRow v-if="useAuthStore().areShortsEnabled()"/>


            <VideosRow :videos="musicPinned" title="Top in Music">
                <font-awesome-icon :icon="['fas', 'music']" class="my-auto h-6"/>
            </VideosRow>



            <InfiniteVideos :videos="videos" />

        </ConsistentPadding>
    </div>
</template>
