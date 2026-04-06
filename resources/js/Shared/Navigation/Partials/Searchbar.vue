<script setup>
import SearchIcon from '~/images/icons/search.svg';
import CloseNavSVG from '~/images/icons/exit.svg';
import { router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import SearchSuggestion from '@/Shared/Navigation/Partials/SearchSuggestion.vue';
import { useNavStore } from '@/Stores/NavStore';
const navStore = useNavStore();

const name = 'Searchbar';

const onClickAway = () => {
    if (navStore.getExpandedSearchResults()) {
        navStore.toggleExpandedSearchResultsOff();
    }
};

let searchInput = ref('');
let results = ref([]);
let selectedResultIndex = ref(-1);

watch(searchInput, (value) => {
    selectedResultIndex.value = -1;
    axios
        .get(route('api.search.suggestions', { q: value }))
        .then((response) => {
            results.value = response.data;
        })
        .catch((error) => {
            console.log(error);
        });
});

function searchEntered() {
    if (
        searchInput.value.length > 0 &&
        (window.innerWidth > 640 || navStore.getExpandedSearchResults())
    ) {
        router.visit(route('search', { q: searchInput.value }));
        navStore.toggleExpandedSearchResultsOff();
    }
}

const showSearchResultsDropdown = computed(() => {
    const r = results.value || {};
    return (
        navStore.getExpandedSearchResults() &&
        Object.keys(r)
            .filter((key) => key !== 'query')
            .reduce((accumulator, key) => {
                const arr = r[key];
                return accumulator + (Array.isArray(arr) ? arr.length : 0);
            }, 0) > 0
    );
});

function selectResult(index) {
    selectedResultIndex.value = index;
}

function suggestionListLength() {
    const r = results.value || {};
    const keys = ['categories', 'streams', 'creators', 'videos', 'podcasts', 'playlists'];
    return keys.reduce((n, k) => n + (Array.isArray(r[k]) ? r[k].length : 0), 0);
}

function selectNextResult() {
    if (showSearchResultsDropdown.value) {
        const len = suggestionListLength();
        if (len) {
            selectedResultIndex.value = (selectedResultIndex.value + 1) % len;
        }
    }
}

function selectPreviousResult() {
    const len = suggestionListLength();
    if (showSearchResultsDropdown.value && len) {
        selectedResultIndex.value = (selectedResultIndex.value - 1 + len) % len;
    }
}

function goToSelectedResult() {
    if (showSearchResultsDropdown.value && selectedResultIndex.value >= 0) {
        const resultTypes = ['categories', 'streams', 'creators', 'videos', 'podcasts', 'playlists'];
        const r = results.value || {};
        const allResults = resultTypes.flatMap((type) => (Array.isArray(r[type]) ? r[type] : []));
        const selectedResult = allResults[selectedResultIndex.value];

        if (selectedResult) {
            const selectedResultValue = selectedResult.name || selectedResult.title;
            if (selectedResultValue === searchInput.value || !selectedResultValue) {
                searchEntered();
            } else {
                searchInput.value = selectedResultValue;
            }
        }
    } else {
        searchEntered();
        navStore.toggleExpandedSearchResultsOff();
    }
}

const query = new URLSearchParams(window.location.search);
searchInput.value = query.get('q') || '';
</script>

<template>
    <div class="flex flex-grow flex-col items-end justify-center sm:items-center sm:px-5">
        <div class="relative flex w-full flex-row justify-end space-x-3 sm:justify-center">
            <div
                class="p-2 pl-1"
                :class="{
                    hidden: !navStore.getExpandedSearchBar(),
                    flex: navStore.getExpandedSearchBar(),
                }"
            >
                <CloseNavSVG
                    class="ml-1 my-auto aspect-square w-6 flex-shrink-0 cursor-pointer text-white"
                    @click="navStore.toggleShowingNavigationDropdownOff()"
                />
            </div>

            <div
                v-click-away="onClickAway"
                class="relative flex flex-col"
                :class="{
                    'w-full': navStore.getExpandedSearchBar(),
                    'w-max max-w-md sm:w-full': !navStore.getExpandedSearchBar(),
                }"
                @click="navStore.toggleExpandedSearchBarOn()"
            >
                <!-- Input row: shares outline with dropdown (no line between = gap “opening”) -->
                <div
                    class="relative flex h-10 items-center bg-zinc-900/95 px-3 text-zinc-500 sm:gap-x-2"
                    :class="
                        showSearchResultsDropdown
                            ? 'rounded-t-xl border border-white/10 border-b-0'
                            : 'rounded-xl border border-white/10'
                    "
                >
                    <input
                        v-model.trim="searchInput"
                        type="text"
                        class="without-ring m-0 bg-transparent p-0 text-sm font-bold text-white placeholder-zinc-500"
                        :class="{
                            'w-full': navStore.getExpandedSearchBar(),
                            'w-0 sm:w-full': !navStore.getExpandedSearchBar(),
                            'placeholder-zinc-400': navStore.getExpandedSearchResults(),
                        }"
                        placeholder="Search YouTube, Rumble, Twitch, Vimeo and more..."
                        @click="navStore.toggleExpandedSearchResultsOn"
                        @keydown.arrow-down="selectNextResult"
                        @keydown.arrow-up="selectPreviousResult"
                        @keydown.enter="goToSelectedResult"
                    />
                    <SearchIcon class="h-5 w-5 flex-shrink-0 cursor-pointer" @click.stop="searchEntered" />
                </div>

                <!-- Results: same border color/weight; top open (no top border) so it reads as one control -->
                <div
                    v-show="showSearchResultsDropdown"
                    class="z-20 max-h-[min(24rem,70vh)] overflow-y-auto rounded-b-xl border border-t-0 border-white/10 bg-zinc-900/95 -mt-px px-3 py-2 text-sm text-white shadow-lg"
                >
                    <div
                        class="flex cursor-pointer flex-col space-y-1 text-left"
                        @click="goToSelectedResult"
                    >
                        <SearchSuggestion
                            v-for="(category, index) in results.categories || []"
                            :key="category.id"
                            :link="'search?q=' + category.name"
                            :text="category.name"
                            :selected="selectedResultIndex === index"
                            @mouseover="selectResult(index)"
                        />
                        <SearchSuggestion
                            v-for="(stream, index) in results.streams || []"
                            :key="stream.id"
                            :link="'search?q=' + stream.title"
                            :text="stream.title"
                            :selected="selectedResultIndex === index + (results.categories || []).length"
                            @mouseover="selectResult(index + (results.categories || []).length)"
                        />
                        <SearchSuggestion
                            v-for="(creator, index) in results.creators || []"
                            :key="creator.id"
                            :link="'search?q=' + creator.name"
                            :text="creator.name"
                            :selected="
                                selectedResultIndex ===
                                index + (results.categories || []).length + (results.streams || []).length
                            "
                            @mouseover="
                                selectResult(index + (results.categories || []).length + (results.streams || []).length)
                            "
                        />
                        <SearchSuggestion
                            v-for="(video, index) in results.videos || []"
                            :key="video.id"
                            :link="'search?q=' + video.title"
                            :text="video.title"
                            :selected="
                                selectedResultIndex ===
                                index +
                                    (results.categories || []).length +
                                    (results.streams || []).length +
                                    (results.creators || []).length
                            "
                            @mouseover="
                                selectResult(
                                    index +
                                        (results.categories || []).length +
                                        (results.streams || []).length +
                                        (results.creators || []).length
                                )
                            "
                        />
                        <SearchSuggestion
                            v-for="(podcast, index) in results.podcasts || []"
                            :key="podcast.id"
                            :link="'search?q=' + podcast.title"
                            :text="podcast.title"
                            :selected="
                                selectedResultIndex ===
                                index +
                                    (results.categories || []).length +
                                    (results.streams || []).length +
                                    (results.creators || []).length +
                                    (results.videos || []).length
                            "
                            @mouseover="
                                selectResult(
                                    index +
                                        (results.categories || []).length +
                                        (results.streams || []).length +
                                        (results.creators || []).length +
                                        (results.videos || []).length
                                )
                            "
                        />
                        <SearchSuggestion
                            v-for="(playlist, index) in results.playlists || []"
                            :key="playlist.id"
                            :link="'search?q=' + playlist.title"
                            :text="playlist.title"
                            :selected="
                                selectedResultIndex ===
                                index +
                                    (results.categories || []).length +
                                    (results.streams || []).length +
                                    (results.creators || []).length +
                                    (results.videos || []).length +
                                    (results.podcasts || []).length
                            "
                            @mouseover="
                                selectResult(
                                    index +
                                        (results.categories || []).length +
                                        (results.streams || []).length +
                                        (results.creators || []).length +
                                        (results.videos || []).length +
                                        (results.podcasts || []).length
                                )
                            "
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
