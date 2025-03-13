

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
const cryptoPinned = ref([]);
const wealthPinned = ref([]);
const alternatePinned = ref([]);

const categorySlugs = {
    'music': 'music',
    'crypto': 'crypto_currency',
    'wealth': 'wealth_inequality',
    'alternate': 'alternate_news'
};



onMounted(async () => {
    // vimeoPinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'Vimeo');
    // rumblePinned.value = await pinModalStore.getPinnedVideos(6, 1, null, 'Rumble');
    musicPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.music);
    cryptoPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.crypto);
    wealthPinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.wealth);
    alternatePinned.value = await pinModalStore.getPinnedVideos(6, 1, categorySlugs.alternate);


    await fetchTrendingVideos().then(async () => {
        await debouncedFetchVideos(); // call it immediately on mount

        window.addEventListener('scroll', handleScroll);
    });

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
    if (scrollPosition >= bodyHeight - 800) {
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



<!--            <VideosRow :videos="vimeoPinned" title="Vimeo Spotlight">-->
<!--                <VimeoIcon class="my-auto h-6"/>-->
<!--            </VideosRow>-->

<!--            <VideosRow :videos="rumblePinned" title="Hot on Rumble">-->
<!--                <RumbleIcon class="my-auto h-6"/>-->
<!--            </VideosRow>-->

            <VideosRow :videos="musicPinned" title="Top in Music">
                <font-awesome-icon :icon="['fas', 'music']" class="my-auto h-6"/>
            </VideosRow>

            <VideosRow :videos="wealthPinned" title="Wealth Inequality" :showCategoryTag="false">
                <img src="/images/monoply.png" class="my-auto h-12 -mt-1"/>
            </VideosRow>

            <VideosRow :videos="cryptoPinned" title="Crypto Currency" :showCategoryTag="false">
                <font-awesome-icon :icon="['fas', 'coins']" class="my-auto h-6"/>
            </VideosRow>

            <VideosRow :videos="alternatePinned" title="Alternate News" :showCategoryTag="false">
                <font-awesome-icon :icon="['fas', 'newspaper']" class="my-auto h-6"/>
            </VideosRow>

            <TopShortsRow v-if="useAuthStore().areShortsEnabled()"/>






            <InfiniteVideos :videos="videos" />

        </ConsistentPadding>
    </div>
</template>
