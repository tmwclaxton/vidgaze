<script setup>
import axios from "axios";
import {computed, onMounted, onUnmounted, ref} from 'vue';
import CreatorSearchCard from "@/Components/Cards/CreatorSearchCard/CreatorSearchCard.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import CreatorSearchSkeleton from "@/Components/Cards/CreatorSearchCard/CreatorSearchSkeleton.vue";
import VideoStreamSearchCard from "@/Components/Cards/VideoStreamCards/VideoStreamSearchCard/VideoStreamSearchCard.vue";
import VideoStreamSearchSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamSearchCard/VideoStreamSearchSkeleton.vue";
import ErrorMessage from "@/Components/Errors/ErrorMessage.vue";

const props = defineProps({
    searchQuery: String,
});

let filters = ref(false);
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
    // console.log(visibleCreatorsCount.value);
};

const loading = ref(true);
const creators = ref([]);
const videos = ref([]);
const playlists = ref([]);
const podcasts = ref([]);
const streams = ref([]);
// platform searches finished so far, from get_results
const progress = ref({done: 0, total: 0, complete: false});
const finished = ref(false);

const hasResults = computed(() => creators.value.length > 0 || videos.value.length > 0);

function startSearch(searchQuery) {
    return axios.post(route('api.search.start_query', {q: searchQuery.value}))
        .catch(error => console.log(error));
}

async function retrieveResults(searchQuery) {
    try {
        const response = await axios.get(route('api.search.get_results', {q: searchQuery.value}));
        const data = response.data;
        creators.value = data.creators?.data ?? [];
        videos.value = data.videos?.data ?? [];
        playlists.value = data.playlists?.data ?? [];
        podcasts.value = data.podcasts?.data ?? [];
        streams.value = data.streams?.data ?? [];
        if (data.progress) {
            progress.value = data.progress;
        }
        // show results as soon as there are any, even while other platforms are still searching
        if (hasResults.value) {
            loading.value = false;
        }
        if (data.progress?.complete) {
            finish();
        }
    } catch (error) {
        console.log(error);
    }
}

let interval = null;
let timeout = null;
function finish() {
    clearInterval(interval);
    clearTimeout(timeout);
    finished.value = true;
    loading.value = false;
}

onMounted(async () => {
    await startSearch(searchQuery);
    await retrieveResults(searchQuery);
    if (finished.value) return;

    // poll quickly so each platform's results appear as soon as they are ready
    interval = setInterval(() => retrieveResults(searchQuery), 1500);
    // give up waiting after 25 seconds
    timeout = setTimeout(finish, 25000);
});

onUnmounted(() => {
    clearInterval(interval);
    clearTimeout(timeout);
});
</script>
<template>
    <Head :title="searchQuery ? `Search: ${searchQuery}` : 'Search'" />

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
                    <p class="font-bold uppercase w-24 text-sm">Platform</p>
                    <!--<x-hr class="my-3"/>-->
                    <p>YouTube</p>
                    <p>Dailymotion</p>
                    <p>Rumble</p>
                    <p>Twitch</p>
                    <p>Odysee</p>
                    <p>Vimeo</p>

                </div>
                <div class="flex flex-col gap-y-1">
                    <p class="font-bold uppercase w-24 text-sm">Upload date</p>
                    <!--<x-hr class="my-3"/>-->
                    <p>Last hour</p>
                    <p>Today</p>
                    <p>This week</p>
                    <p>This month</p>
                    <p>This year</p>
                </div>
                <div class="flex flex-col gap-y-1">
                    <p class="font-bold uppercase w-24 text-sm">Type</p>
                    <!--<x-hr class="my-3"/>-->
                    <p>Video</p>
                    <p>Channel</p>
                    <p>Playlist</p>
                </div>
                <div class="flex flex-col gap-y-1">
                    <p class="font-bold uppercase w-24 text-sm">Duration</p>
                    <!--<x-hr class="my-3"/>-->
                    <p>Under 4 minutes</p>
                    <p>4 - 20 minutes</p>
                    <p>Over 20 minutes</p>

                </div>
                <div class="flex flex-col gap-y-1">
                    <p class="font-bold uppercase w-24 text-sm">Sort by</p>
                    <!--<x-hr class="my-3"/>-->
                    <p>Relevance</p>
                    <p>Upload date</p>
                    <p>View count</p>
                    <p>Rating</p>

                </div>

            </div>
        </div>


        <div >
            <section class="mt-4">

                <div
                    class="flex flex-col flex-wrap gap-4 ">
                    <CreatorSearchCard v-if="creators.length > 0" v-for="creator in creators.slice(0,visibleCreatorsCount)" :key="creator.id" :creator="creator"/>
                    <CreatorSearchSkeleton v-else v-for="i in 2" v-if="loading"/>
                </div>

                <RowDivider v-if="creators.length > 2  && !loading" @click="toggleVisibleCreators"
                            class="mt-4 mb-4"  :text="expandCreators ? 'Show less' : 'Show more'">
                    <font-awesome-icon v-if="expandCreators" :icon="['fas', 'caret-up']" />
                    <font-awesome-icon v-if="!expandCreators" :icon="['fas', 'caret-down']" />
                </RowDivider>

                <RowDivider v-else class="mt-4 mb-4" v-if="loading || (creators.length > 0 && creators.length < 3)"/>

                <!--progress while platforms are still being searched-->
                <div v-if="!finished" role="status" aria-live="polite"
                     class="flex items-center gap-3 mb-4 text-base text-zinc-600 dark:text-zinc-300">
                    <font-awesome-icon :icon="['fas', 'spinner']" class="h-4 animate-spin" aria-hidden="true"/>
                    <span v-if="progress.total > 0">Searching sources ({{ progress.done }} of {{ progress.total }} done)</span>
                    <span v-else>Searching</span>
                    <span v-if="hasResults">, more results may appear</span>
                </div>

                <div class="px-0 relative w-full grid grid-cols-1 gap-4 ">
                    <VideoStreamSearchCard v-if="videos.length > 0" v-for="video in videos" :key="video.id" :item="video"/>
                    <VideoStreamSearchSkeleton v-else-if="loading" v-for="i in 8"/>
                </div>

                <div class="mt-20" v-if="finished && !hasResults">
                    <ErrorMessage :message="`No results for '${searchQuery}'. Try a different search.`"/>
                </div>

            </section>


        </div>

    </div>
</template>
