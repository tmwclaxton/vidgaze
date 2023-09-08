
<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import {onMounted, ref} from "vue";
import Badge from "@/Components/General/Badge.vue";
import {useShareModalStore} from "@/Stores/ShareModelStore";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import ChannelButton from "@/Pages/Viewer/Channel/Partials/ChannelButton.vue";
import ChannelHome from "@/Pages/Viewer/Channel/Partials/ChannelHome.vue";
import ChannelVideos from "@/Pages/Viewer/Channel/Partials/ChannelVideos.vue";
import _, {debounce} from "lodash";
import ChannelPlaylists from "@/Pages/Viewer/Channel/Partials/ChannelPlaylists.vue";
import ChannelAbout from "@/Pages/Viewer/Channel/Partials/ChannelAbout.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import StudioLink from "@/Pages/Viewer/Channel/Partials/StudioLink.vue";
import {useAuthStore} from "@/Stores/AuthStore";
import FeatureCreatorButton from "@/Components/Buttons/FeatureCreatorButton.vue";

const name = 'Channel';
const channel = ref(null);
const channelLoading = ref(true);
const props = defineProps({
    slug: {
        type: String,
        required: true
    }
});

const fetchChannel = async () => {
    // console.log('fetching channel');
    channelLoading.value = true;
    axios.get(route('api.creator.show', {slug: props.slug}))
        .then((response) => {
            channel.value = response.data.creator
            channelLoading.value = false;
        })
        .catch((error) => {
            console.log(error);
        });
};

onMounted(() => {
    fetchChannel().then(() => {
        setTimeout(() => {
            fetchVideos();
        }, 1000);
    });
});

const showShare = ref(false);
function share() {
    if (showShare.value) {
        useShareModalStore().showMenu = false;
    } else {
        useShareModalStore().showMenu = true;
        let link = route('channel.show', {slug: this.channel.slug});
        let title = "Check out this cool channel on VidGaze";
        useShareModalStore().getShareLinks(link, title);
    }
    showShare.value = !showShare.value;
};
const tab = ref('home');

// make axios request to channel videos api
const videos = ref([]);
const page = ref(null);
const fetchVideos = async () => {
    if (page.value === null && videos.value.length > 0) {
        console.log('no more videos');
        return;
    }

    // console.log(page.value)
    const result = await useContentRoutesStore().getChannelVideos(channel.value, 30, page.value);
    if (result.videos.length === 0) {
        return;
    }

    // treat videos as a set, so no duplicates
    const videoIds = videos.value.map(video => video.id);
    result.videos = result.videos.filter(video => !videoIds.includes(video.id));

    videos.value = [...videos.value, ...result.videos];
    page.value = result.nextPage;
};

</script>



<template>
    <Head>
        <title></title>
    </Head>

        <div v-if="channel" class="flex flex-col flex-grow ">
            <div class="relative flex flex-row  bg-zinc-50  max-h-64 overflow-hidden">
                <img class=" flex-grow object-cover"
                     v-bind:src="channel.banner_url" alt="">
                    <div @click="share()"
                        class=" cursor-pointer absolute bg-zinc-900 border border-zinc-600 p-2 px-4 bg-opacity-70 rounded
                        top-5 right-5 font-bold gap-x-3 flex flex-row">
                        <font-awesome-icon :icon="['fas', 'share-alt']" class="w-4 text-white my-auto"/>
                        <p class="hidden md:flex opacity-100 text-white select-none">Share VidGaze Channel</p>
                    </div>

            </div>

            <div class="   py-5  px-5 lg:px-10 generic-background_2 dark:generic-background-dark_2  ">
                <div class="flex flex-row justify-center sm:justify-start px-2 overflow-hidden h-max">

                    <img class="bg-white   flex-shrink-0  h-20 aspect-square rounded-full "
                        v-bind:src="channel.avatar_url" alt="">

                    <div class="flex flex-col sm:flex-row  overflow-hidden w-full h-full ">
                        <div class=" flex flex-col w-full  sm:w-64 md:w-64  flex-shrink md:my-auto pl-5">
                            <div class="flex flex-row flex-wrap gap-x-4">
                                <p class=" font-semibold text-xl break-words" v-text="channel.name"></p>
                                <SubscribeButton :channel="channel" class="flex-shrink-0 mt-0.5"/>
                            </div>

                            <span class="" v-text="channel.subscribers_count"></span>
                            <div class="mt-1 inline-flex space-x-1">
                                <Badge v-for="source in channel.sources" :key="source" :text="source" :source="source" v-if="channel.sources[0] != null"/>
                            </div>
                        </div>
                         <!--this is here for design fix -->
                        <div class="h-20 hidden sm:flex">
                            <div></div>
                        </div>
                        <div
                            class="flex flex-row gap-x-1 ml-auto mt-2">
                            <StudioLink v-if="useAuthStore().user && useAuthStore().user.creator.slug === channel.slug" text="Customise Channel" :link="route('studio.customise')"/>
                            <StudioLink v-if="useAuthStore().user && useAuthStore().user.creator.slug === channel.slug" text="Manage Videos" :link="route('studio.content')"/>
                            <FeatureCreatorButton v-if="useAuthStore().admin" :creator_id="channel.id"/>

                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-row flex-wrap items-center just ify-center px-auto gap-x-2 z-10 sm:px-5 lg:px-10 bg-zinc-50  dark:bg-zinc-900  text-sm font-bold text-center text-zinc-500  dark:text-zinc-200 ">
                <ChannelButton :currentTab="tab" :tab="'home'" @changePage="tab = 'home'"/>
                <ChannelButton :currentTab="tab" :tab="'videos'" @changePage="tab = 'videos'"/>
                <ChannelButton :currentTab="tab" :tab="'playlists'" @changePage="tab = 'playlists'"/>
                <ChannelButton :currentTab="tab" :tab="'about'" @changePage="tab = 'about'"/>
            </div>

            <div class="pt-5 px-5 lg:px-10 pb-10 h-full">
                <ChannelHome v-if="tab === 'home'" :channel="channel" :videos="videos" />
                <ChannelVideos v-if="tab === 'videos'" :videos="videos" @fetchVideos="fetchVideos" :key="channel.slug"/>
                <ChannelPlaylists v-if="tab === 'playlists'" :channel="channel" />
                <ChannelAbout v-if="tab === 'about'" :channel="channel" />



            </div>
        </div>




</template>
