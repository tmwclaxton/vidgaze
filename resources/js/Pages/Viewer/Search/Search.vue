<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
export default {
    components: {PaddingLayout},
    layout: AuthenticatedLayout,

};
</script>
<script setup>
import axios from "axios";
import {computed, ref} from 'vue';
import CreatorSearchCard from "@/Components/Cards/CreatorSearchCard/CreatorSearchCard.vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCard/VideoStreamCard.vue";
import {shuffle} from "lodash";
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import HorizontalLineText from "@/Components/General/HorizontalLineText.vue";
import CreatorSearchSkeleton from "@/Components/Cards/CreatorSearchCard/CreatorSearchSkeleton.vue";
import VideoStreamSearchCard from "@/Components/Cards/VideoStreamSearchCard/VideoStreamSearchCard.vue";

const props = defineProps({
    searchQuery: String,
});

const filters = ref(false);
const searchQuery = ref(props.searchQuery);

// show 2 creators by default and expand to show all
//set default state, could be based on some attributes
const visibleCreatorsCount = ref(2);
const expandCreators = ref(false);

// computed property to return the visible creators
const toggleVisibleCreators = () => {
    expandCreators.value = !expandCreators.value;
    // if expandCreators is true, return all creators else return the first 2 creators
    visibleCreatorsCount.value = expandCreators.value ? creators.value.length : 2;
    console.log(visibleCreatorsCount.value);
};


search(searchQuery,1);

const creators = ref([]);
const videos = ref(null);
const playlists = ref(null);
const podcasts = ref(null);
const streams = ref(null);

function search(searchQuery,page = 1) {

    // make api call to get search results using ziggy search_query and query params q
    const url = route('search_query', {q: searchQuery.value});

    axios.get(url)
        .then(function (response) {
            // handle success
            // console.log(response);
            creators.value = response.data.creators.data;
            videos.value = shuffle(response.data.videos.data);
            playlists.value = response.data.playlists.data;
            podcasts.value = response.data.podcasts.data;
            streams.value = response.data.streams.data;



        })
        .catch(function (error) {
            // handle error
            console.log(error);
        })
        .then(function () {
            // always executed


        });

}







</script>



<template>
    <Head>
        <title>Search</title>
    </Head>



        <div class=" mx-auto pb-10 pt-4 px-6 lg:px-16">
                <!--filter button-->
                <button v-if="false" class="mb-2 flex text-left ml-1 text-base  "
                        v-on:click="filters = !filters" >
                    <!--<x-icon name="filters" class=" text dark:textDark  subheading flex-shrink-0 w-4 aspect-square  "/>-->
                    <span class="uppercase text dark:textDark text-sm  my-auto  ml-3 whitespace-nowrap font-bold"> Filters</span>
                </button>
                <!--filter options-->
                <div v-show="false" class="text dark:textDark text-sm my-8 mt-5 ml-1">
                    <div class="w-full grid grid-cols-3 gap-7 sm:flex sm:flex-row ">
                        <div class="flex flex-col gap-y-1">
                            <p class="font-bold uppercase w-24 text-xs">Platform</p>
                            <!--<x-hr class="my-3"/>-->
                            <p>YouTube</p>
                            <p>Dailymotion</p>
                            <p>Rumble</p>
                            <p>Twitch</p>
                            <p>Odysee</p>
                            <p>Vimeo</p>

                        </div>
                        <div class="flex flex-col gap-y-1">
                            <p class="font-bold uppercase w-24 text-xs">Upload date</p>
                            <!--<x-hr class="my-3"/>-->
                            <p>Last hour</p>
                            <p>Today</p>
                            <p>This week</p>
                            <p>This month</p>
                            <p>This year</p>
                        </div>
                        <div class="flex flex-col gap-y-1">
                            <p class="font-bold uppercase w-24 text-xs">Type</p>
                            <!--<x-hr class="my-3"/>-->
                            <p>Video</p>
                            <p>Channel</p>
                            <p>Playlist</p>
                        </div>
                        <div class="flex flex-col gap-y-1">
                            <p class="font-bold uppercase w-24 text-xs">Duration</p>
                            <!--<x-hr class="my-3"/>-->
                            <p>Under 4 minutes</p>
                            <p>4 - 20 minutes</p>
                            <p>Over 20 minutes</p>

                        </div>
                        <div class="flex flex-col gap-y-1">
                            <p class="font-bold uppercase w-24 text-xs">Sort by</p>
                            <!--<x-hr class="my-3"/>-->
                            <p>Relevance</p>
                            <p>Upload date</p>
                            <p>View count</p>
                            <p>Rating</p>

                        </div>

                    </div>
                </div>


                <div >
                    <section v-if="true" class="mt-4">

                        <!--<RowDivider/>-->

                        <div class="flex flex-col flex-wrap gap-4 ">
                            <CreatorSearchCard v-if="creators.length > 0" v-for="creator in creators.slice(0,visibleCreatorsCount)" :creator="creator"/>
                            <CreatorSearchSkeleton v-else v-for="i in 2"/>
                        </div>

                        <RowDivider v-if="creators.length > 2" class="mt-4 mb-4" @click="toggleVisibleCreators" :text="expandCreators ? 'Show less' : 'Show more'">
                            <font-awesome-icon v-if="expandCreators" :icon="['fas', 'caret-up']" />
                            <font-awesome-icon v-if="!expandCreators" :icon="['fas', 'caret-down']" />
                        </RowDivider>

                        <RowDivider v-else class="mt-4 mb-4" />

                        <div class="px-0 relative w-full grid grid-cols-1 gap-4 ">
                            <!--<x-search-video-card :video="$video"/>-->
                            <VideoStreamSearchCard v-for="video in videos" :item="video"/>

                        </div>

                    </section>


                </div>

        </div>




</template>
