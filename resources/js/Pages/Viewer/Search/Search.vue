<template>
    <Head>
        <title>Search</title>
    </Head>



        <div class=" mx-auto pb-10 pt-4 px-6 lg:px-16">
            <section v-if="results != null" >
                <!--filter button-->
                <button v-if="results != null" class="mb-2 flex text-left ml-1 text-base  "
                        v-on:click="filters = !filters" >
                    <!--<x-icon name="filters" class=" text dark:textDark  subheading flex-shrink-0 w-4 aspect-square  "/>-->
                    <span class="uppercase text dark:textDark text-sm  my-auto  ml-3 whitespace-nowrap font-bold"> Filters</span>
                </button>
                <!--filter options-->
                <div v-show="filters" class="text dark:textDark text-sm my-8 mt-5 ml-1">
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

                <!--<x-hr class="mt-2 mb-2"/>-->

                <!--<livewire:search-results :searchQuery="$searchQuery"/>-->
                <div >
                    <section v-if="results != null">
                        <p v-if="searchQuery" class="text dark:textDark text-base font-bold my-3">Results for: {{ searchQuery}}</p>
                        <p class="text dark:textDark text-base font-bold my-3">Creators</p>
                        <CreatorSearchCard v-for="creator in results.creators" :creator="creator"/>
                        <!--<x-channel-card :creator="$creator"/>-->
                        <!--<x-hr class="mt-2 mb-2"/>-->

                        <p class="text dark:textDark text-base font-bold my-3">Streams</p>
                        <div class="px-0 relative w-full  ">
                            <!--<x-search-stream-card :stream="$stream"/>-->

                        </div>
                        <p class="text dark:textDark text-base font-bold my-3">Videos</p>
                        <div class="px-0 relative w-full  ">
                            <!--<x-search-video-card :video="$video"/>-->

                        </div>
                    </section>


                </div>
            </section>

            <section v-if="results == null">
                <!--<x-error-message text="Loading..."/>-->
                <!--<x-skeleton-profile/>-->
                <!--<x-skeleton-video/>-->
                <!--<x-skeleton-video/>-->
                <!--<x-skeleton-video/>-->

            </section>

        </div>




</template>
<script setup>
import axios from "axios";
import { ref } from 'vue';
import CreatorSearchCard from "@/Pages/Viewer/Search/Partials/CreatorSearchCard.vue";
const props = defineProps({
    name: String,
    searchQuery: String,
    results: Object,

});
let filters = ref(false);
let results = ref(props.results);
const searchQuery = ref(props.searchQuery);

search(searchQuery,1);


// make api call to get search results
// await axios.get('/api/search/' + searchQuery);

function search(searchQuery,page = 1) {
// get search query
// make api call to get search results
axios.get('/api/search?q=' + searchQuery.value + '&page=' + page)
    .then(function (response) {
        // handle success
        console.log(response);
        results.value = response.data;
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

<style scoped>

</style>
