<script setup>
import SearchIcon from '~/images/icons/search.svg';
import CloseNavSVG from '~/images/icons/exit.svg';
import { Link } from '@inertiajs/vue3';
import { defineProps, defineEmits, ref, watch, computed } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import SearchSuggestion from '@/Shared/SearchDropdown/SearchSuggestion.vue';

// name of the component
const name = 'Searchbar';

// accept props
const props = defineProps({
    expandedSearchBar: {
        type: Boolean,
        required: true
    },
    expandedSearchResults: {
        type: Boolean,
        required: true
    }
});

// define emits
const emits = defineEmits([
    'toggleExpandedSearchBarOff',
    'toggleExpandedSearchResultsOff',
    'toggleExpandedSearchBarOn',
    'toggleExpandedSearchResultsOn'
]);

const onClickAway = event => {
    if (props.expandedSearchResults) {
        emits('toggleExpandedSearchResultsOff');
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
        .get('/search-bar', { params: { q: value } })
        .then(response => {
            results.value = response.data;
        })
        .catch(error => {
            console.log(error);
        });
});

function searchEntered() {
    // console.log(searchInput.value);
    if (searchInput.value.length > 0) {
        Inertia.get('/search', { q: searchInput.value });
    }
}

const showSearchResultsDropdown = computed(() => {
    return (
        props.expandedSearchResults &&
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
    }
}


</script>

<template>

    <!--Search bar-->
    <div
         class="flex flex-col flex-grow  justify-center items-end sm:items-center sm:px-5">
        <div
            class="relative flex flex-row space-x-3 w-full justify-end sm:justify-center">

            <div class="p-2 pl-1  " :class="{
                                            'hidden': !expandedSearchBar,
                                            ' flex': expandedSearchBar,
                                        }">
                <!--Exit expanded search-->
                <CloseNavSVG @click="$emit('toggleExpandedSearchBarOff')"
                             class="ml-1 w-6 aspect-square flex-shrink-0 text-white   my-auto"/>
            </div>

            <div  v-click-away="onClickAway" @click="$emit('toggleExpandedSearchBarOn')"
                  :class="{'w-full  ': expandedSearchBar,' w-max sm:w-full max-w-md ': !expandedSearchBar,
                'rounded-t-md rounded-r-md  ': showSearchResultsDropdown ,' rounded-md ': !showSearchResultsDropdown}"
                 class="h-10 relative flex sm:gap-x-2 items-center text-zinc-500 px-3 bg-zinc-900 ">
                <input type="text"
                       @click="$emit('toggleExpandedSearchResultsOn')"
                       v-model.trim="searchInput"
                       @keydown.arrow-down="selectNextResult"
                       @keydown.arrow-up="selectPreviousResult"
                       @keydown.enter="goToSelectedResult"
                       class="bg-transparent p-0 m-0 without-ring placeholder-zinc-500 text-white font-bold text-sm"
                       :class="{'w-full': expandedSearchBar,'w-0 sm:w-full': !expandedSearchBar,' placeholder-zinc-400': expandedSearchResults}"
                       placeholder="Search YouTube, Twitch, Odysee and more...">
                <SearchIcon @click="searchEntered" class="w-5 h-5 flex-shrink-0"/>

                <!--Search dropdown-->
                <div  :class="{'w-full': expandedSearchBar,' w-max sm:w-full max-w-md': !expandedSearchBar,
                    'flex ': expandedSearchResults ,'hidden': !showSearchResultsDropdown}"
                          class="absolute left-0 top-9 w-full pr-11 sm:pl-0  ">

                    <div class="relative w-full bg-zinc-900 dark:bg-zinc-900 border border-zinc-900 h-full
                               py-2 px-3  rounded-b-xl text-white shadow shadow-md  ">
                        <div class="relative w-full fixed pointer-events absolute rounded-none inset-x-0 mx-auto z-20 ">
                            <div class=" w-full text-sm text-left flex flex-col space-y-1">
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

        </div>


    </div>


</template>
