<script setup>
import SearchIcon from '~/images/icons/search.svg';
import CloseNavSVG from '~/images/icons/exit.svg';
import {Link, router} from '@inertiajs/vue3';
import { defineProps, defineEmits, ref, watch, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import SearchSuggestion from '@/Shared/Navigation/Partials/SearchSuggestion.vue';
import {useNavStore} from "@/Stores/NavStore";
const navStore = useNavStore();

// name of the component
const name = 'Searchbar';



const onClickAway = event => {
    if ( navStore.getExpandedSearchResults() ) {
        navStore.toggleExpandedSearchResultsOff();
    }
};

// search query
let searchInput = ref('');
let results = ref([]);
let selectedResultIndex = ref(-1);

watch(searchInput, value => {
    // reset the selected result index whenever a new search query is entered
    selectedResultIndex.value = -1;
    axios
        .get('/search_suggestions', { params: { q: value } })
        .then(response => {
            results.value = response.data;
        })
        .catch(error => {
            console.log(error);
        });
});

function searchEntered() {
    // console.log(searchInput.value);
    // if (searchInput.value.length > 0 && ( expandedSearchResults is oepn when in mobile view so width < 4

    if (searchInput.value.length > 0 && (window.innerWidth > 640  || navStore.getExpandedSearchResults())) {
        // Inertia.get('/search', { q: searchInput.value }); // can't use this because it does a full page reload
        router.visit('/search?q=' + searchInput.value);
        // close the search results dropdown
        navStore.toggleExpandedSearchResultsOff();
    }
}

const showSearchResultsDropdown = computed(() => {
    return (
        navStore.getExpandedSearchResults() &&
        Object.keys(results.value)
            .filter(key => key !== 'query')
            .reduce((accumulator, key) => {
                return accumulator + results.value[key].length;
            }, 0) > 0
    );
});

function selectResult(index) {
    selectedResultIndex.value = index;
}

function selectNextResult() {
    if (showSearchResultsDropdown.value) {
        selectedResultIndex.value =
            (selectedResultIndex.value + 1) %
            (results.value.categories.length +
                results.value.streams.length +
                results.value.creators.length +
                results.value.videos.length +
                results.value.podcasts.length +
                results.value.playlists.length);
    }
}

function selectPreviousResult() {
    const lengthOfAll =
        results.value.categories.length +
        results.value.streams.length +
        results.value.creators.length +
        results.value.videos.length +
        results.value.podcasts.length +
        results.value.playlists.length;

    if (showSearchResultsDropdown.value) {
        selectedResultIndex.value =
            (selectedResultIndex.value - 1 +
                lengthOfAll) %
            (lengthOfAll);
    }
}
function goToSelectedResult() {
    if (showSearchResultsDropdown.value && selectedResultIndex.value >= 0) {
        const resultTypes = ['categories', 'streams', 'creators', 'videos', 'podcasts', 'playlists'];
        const allResults = resultTypes.map(type => results.value[type]).flat();
        const selectedResult = allResults[selectedResultIndex.value];

        if (selectedResult) {
            const selectedResultValue = selectedResult.name || selectedResult.title;
            if (selectedResultValue === searchInput.value || !selectedResultValue) {
                searchEntered();
            } else {
                searchInput.value = selectedResultValue;
            }
        }
    }else {
        searchEntered();
        // close the search results dropdown
        navStore.toggleExpandedSearchResultsOff();
    }
}

// on mount get the search query from the url 'q' parameter
const query = new URLSearchParams(window.location.search);
searchInput.value = query.get('q') || '';


</script>

<template>

    <!--Search bar-->
    <div
         class="flex flex-col flex-grow  justify-center items-end sm:items-center sm:px-5">
        <div
            class="relative flex flex-row space-x-3 w-full justify-end sm:justify-center">

            <div class="p-2 pl-1  " :class="{
                                            'hidden': !navStore.getExpandedSearchBar(),
                                            ' flex': navStore.getExpandedSearchBar(),
                                        }">
                <!--Exit expanded search-->
                <CloseNavSVG @click="navStore.toggleShowingNavigationDropdownOff()"
                             class="ml-1 w-6 aspect-square flex-shrink-0 text-white   my-auto"/>
            </div>

            <div  v-click-away="onClickAway" @click="navStore.toggleExpandedSearchBarOn()"
                  :class="{'w-full  ': navStore.getExpandedSearchBar(),' w-max sm:w-full max-w-md ': !navStore.getExpandedSearchBar(),
                'rounded-t-md rounded-r-md  ': showSearchResultsDropdown ,' rounded-md ': !showSearchResultsDropdown}"
                 class="h-10 relative flex sm:gap-x-2 items-center text-zinc-500 px-3 bg-zinc-900 ">
                <input type="text"
                       @click="navStore.toggleExpandedSearchResultsOn"
                       v-model.trim="searchInput"
                       @keydown.arrow-down="selectNextResult"
                       @keydown.arrow-up="selectPreviousResult"
                       @keydown.enter="goToSelectedResult"
                       class="bg-transparent p-0 m-0 without-ring placeholder-zinc-500 text-white font-bold text-sm"
                       :class="{'w-full': navStore.getExpandedSearchBar(),'w-0 sm:w-full': !navStore.getExpandedSearchBar(),' placeholder-zinc-400': navStore.getExpandedSearchResults()}"
                       placeholder="Joshua you suck Search YouTube, Twitch, Odysee and more...">
                <SearchIcon @click="searchEntered" class="w-5 h-5 flex-shrink-0"/>

                <!--Search dropdown-->
                <div  :class="{'w-full': navStore.getExpandedSearchBar(),' w-max sm:w-full max-w-md': !navStore.getExpandedSearchBar(),
                    'flex ': navStore.getExpandedSearchResults ,'hidden': !showSearchResultsDropdown}"
                          class="absolute left-0 top-9 w-full  sm:pl-0 pointer-events-none ">

                    <div class="relative w-full bg-zinc-900 dark:bg-zinc-900 border border-zinc-900 h-full pointer-events-auto
                               py-2 px-3  rounded-b-xl text-white shadow shadow-md mr-11 ">
                        <div class="relative w-full fixed pointer-events absolute rounded-none inset-x-0 mx-auto z-20 ">
                            <div class=" w-full text-sm text-left flex flex-col space-y-1 cursor-pointer"
                                 @click="goToSelectedResult">
                                <!--for each creator or video in results show a search suggestion-->
                                <!-- for each creator or video in results show a search suggestion -->
                                <SearchSuggestion
                                    v-for="(category, index) in results.categories"
                                    :link="'search?q=' + category.name"
                                    :text="category.name"
                                    :key="category.id"
                                    :selected="selectedResultIndex === index"
                                    @mouseover="selectResult(index)"
                                ></SearchSuggestion>
                                <SearchSuggestion
                                    v-for="(stream, index) in results.streams"
                                    :link="'search?q=' + stream.title"
                                    :text="stream.title"
                                    :key="stream.id"
                                    :selected="selectedResultIndex === index + results.categories.length"
                                    @mouseover="selectResult(index + results.categories.length)"
                                ></SearchSuggestion>
                                <SearchSuggestion
                                    v-for="(creator, index) in results.creators"
                                    :link="'search?q=' + creator.name"
                                    :text="creator.name"
                                    :key="creator.id"
                                    :selected="selectedResultIndex === index + results.categories.length + results.streams.length"
                                    @mouseover="selectResult(index + results.categories.length + results.streams.length)"
                                ></SearchSuggestion>
                                <SearchSuggestion
                                    v-for="(video, index) in results.videos"
                                    :link="'search?q=' + video.title"
                                    :text="video.title"
                                    :key="video.id"
                                    :selected="selectedResultIndex === index + results.categories.length + results.streams.length + results.creators.length"
                                    @mouseover="selectResult(index + results.categories.length + results.streams.length + results.creators.length)"
                                ></SearchSuggestion>
                                <SearchSuggestion
                                    v-for="(podcast, index) in results.podcasts"
                                    :link="'search?q=' + podcast.title"
                                    :text="podcast.title"
                                    :key="podcast.id"
                                    :selected="selectedResultIndex === index + results.categories.length + results.streams.length + results.creators.length + results.videos.length"
                                    @mouseover="selectResult(index + results.categories.length + results.streams.length + results.creators.length + results.videos.length)"
                                ></SearchSuggestion>
                                <SearchSuggestion
                                    v-for="(playlist, index) in results.playlists"
                                    :link="'search?q=' + playlist.title"
                                    :text="playlist.title"
                                    :key="playlist.id"
                                    :selected="selectedResultIndex === index + results.categories.length + results.streams.length + results.creators.length + results.videos.length + results.podcasts.length"
                                    @mouseover="selectResult(index + results.categories.length + results.streams.length + results.creators.length + results.videos.length + results.podcasts.length)"
                                ></SearchSuggestion>

                            </div>


                        </div>
                    </div>
                </div>
            </div>
        <!--<p v-text="selectedResultIndex" class="text-white"></p>-->
        </div>


    </div>


</template>
