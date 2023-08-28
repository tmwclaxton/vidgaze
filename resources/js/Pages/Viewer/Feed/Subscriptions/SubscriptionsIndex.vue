<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import SubscriptionsIcon from '~/images/icons/subscriptions.svg';
import RowDivider from "@/Components/General/RowDivider.vue";
import ErrorMessage from "@/Components/Errors/ErrorMessage.vue";
import {onMounted, ref} from "vue";
import TitleComponent from "@/Components/General/Title.vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";

const name = 'Subscriptions';

const videos = ref([]);
const streams = ref([]);
const podcasts = ref([]);
const loaded = ref(false);

// on mounted grab subscriptions using axios and ziggy
onMounted(() => {
    axios.get(route('api.feed.subscriptions')).then((response) => {
        videos.value = response.data.videos.data;
        streams.value = response.data.streams.data;
        loaded.value = true;
    });
});

</script>



<template>
    <Head>
        <title>Subscriptions</title>
    </Head>


    <ConsistentPadding class="px-12">
        <div class="flex flex-row w-full  justify-center">
            <div class="flex flex-row  flex-grow my-auto">
                <SubscriptionsIcon class="text dark:textDark w-5 aspect-square mr-2 "/>
                <p class="text dark:textDark font-bold text-md h-full my-auto ">Subscriptions</p>
            </div>
            <Link :href="route('feed.channels') " class=" text-center my-auto flex flex-row gap-x-2 ml-auto">
                <font-awesome-icon :icon="['fas', 'gear']" class="my-auto h-5"/>
                <p class="text dark:textDark font-bold text-md h-full ">Manage</p>

            </Link>
        </div>

        <row-divider class="mt-6 mb-3 rounded-2xl"></row-divider>


        <div class="flex flex-col gap-y-2" v-if="streams.length > 0">
            <TitleComponent text="Livestreams" class="my-4 mb-8">
                <font-awesome-icon :icon="['fas', 'fire']" class="w-6 h-6 my-auto" />
            </TitleComponent>

            <div class="px-3 xs:px-0 flex
                        grid grid-cols-1 xs:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5 4xl:grid-cols-6 gap-4 " >
                <VideoStreamCard v-for="stream in streams" :key="stream.id" :item="stream" />
            </div>
        </div>

        <div class="flex flex-col gap-y-2" v-if="videos.length > 0">
            <TitleComponent text="Latest" class="my-4 mb-8">
                <font-awesome-icon :icon="['fas', 'fire']" class="w-6 h-6 my-auto" />
            </TitleComponent>

            <div class="px-3 xs:px-0 flex
                        grid grid-cols-1 xs:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5 4xl:grid-cols-6 gap-4 ">
                <VideoStreamCard v-for="video in videos" :key="video.id" :item="video" />
            </div>
        </div>

        <div class="mt-20" v-if="loaded && videos.length === 0 && streams.length === 0 ">
            <ErrorMessage :message="'Whoops you have no new content'"/>
        </div>

    </ConsistentPadding>




</template>
