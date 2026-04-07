<script setup>
import SearchIcon from '~/images/icons/search.svg';
import CloseNavSVG from '~/images/icons/exit.svg';
import axios from 'axios';
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

const emptySuggestionPayload = {
    videos: [],
    creators: [],
    playlists: [],
    podcasts: [],
    streams: [],
    categories: [],
};

const SUGGEST_DEBOUNCE_MS = 320;
const SUGGEST_MIN_LENGTH = 2;

let suggestionsDebounce = null;
let suggestionsAbort = null;

watch(searchInput, (value) => {
    selectedResultIndex.value = -1;
    if (suggestionsDebounce) {
        clearTimeout(suggestionsDebounce);
    }
    suggestionsAbort?.abort();

    const q = (value ?? '').trim();
    if (q.length < SUGGEST_MIN_LENGTH) {
        results.value = { query: q, ...emptySuggestionPayload };
        return;
    }

    suggestionsDebounce = setTimeout(() => {
        suggestionsDebounce = null;
        suggestionsAbort = new AbortController();
        axios
            .get(route('api.search.suggestions', { q }), { signal: suggestionsAbort.signal })
            .then((response) => {
                results.value = response.data;
            })
            .catch((error) => {
                if (axios.isCancel?.(error) || error?.code === 'ERR_CANCELED') {
                    return;
                }
                console.log(error);
            });
    }, SUGGEST_DEBOUNCE_MS);
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

function selectNextResult() {
    if (showSearchResultsDropdown.value) {
        const c = results.value.categories || [];
        const s = results.value.streams || [];
        const cr = results.value.creators || [];
        const v = results.value.videos || [];
        const p = results.value.podcasts || [];
        const pl = results.value.playlists || [];
        const len =
            c.length + s.length + cr.length + v.length + p.length + pl.length;
        if (len) {
            selectedResultIndex.value = (selectedResultIndex.value + 1) % len;
        }
    }
}

function selectPreviousResult() {
    const c = results.value.categories || [];
    const s = results.value.streams || [];
    const cr = results.value.creators || [];
    const v = results.value.videos || [];
    const p = results.value.podcasts || [];
    const pl = results.value.playlists || [];
    const lengthOfAll = c.length + s.length + cr.length + v.length + p.length + pl.length;
    if (showSearchResultsDropdown.value && lengthOfAll) {
        selectedResultIndex.value =
            (selectedResultIndex.value - 1 + lengthOfAll) % lengthOfAll;
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
                class="relative flex h-10 items-center bg-zinc-900/95 px-3 text-zinc-500 ring-1 ring-white/10 transition-shadow duration-200 ease-out sm:gap-x-2 hover:ring-2 hover:ring-cyan-400/85 focus-within:ring-2 focus-within:ring-cyan-400/90"
                :class="{
                    'w-full': navStore.getExpandedSearchBar(),
                    'w-max max-w-md sm:w-full': !navStore.getExpandedSearchBar(),
                    'rounded-t-xl rounded-r-xl': showSearchResultsDropdown,
                    'rounded-xl': !showSearchResultsDropdown,
                }"
                @click="navStore.toggleExpandedSearchBarOn()"
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
                <SearchIcon
                    class="h-5 w-5 shrink-0 cursor-pointer fill-current text-cyan-400/90 transition-all duration-200 hover:scale-110 hover:text-cyan-300 hover:drop-shadow-[0_0_12px_rgba(34,211,238,0.55)]"
                    @click="searchEntered"
                />

                <div
                    class="pointer-events-none absolute left-0 top-9 w-full sm:pl-0"
                    :class="{
                        'w-full': navStore.getExpandedSearchBar(),
                        'w-max max-w-md sm:w-full': !navStore.getExpandedSearchBar(),
                        flex: navStore.getExpandedSearchResults,
                        hidden: !showSearchResultsDropdown,
                    }"
                >
                    <div
                        class="pointer-events-auto relative mr-11 h-full w-full border border-zinc-900 bg-zinc-900 py-2 px-3 text-white shadow shadow-md dark:bg-zinc-900 rounded-b-xl"
                    >
                        <div class="relative w-full">
                            <div
                                class="flex w-full cursor-pointer flex-col space-y-1 text-left text-sm"
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
                                    :selected="
                                        selectedResultIndex ===
                                        index + (results.categories || []).length
                                    "
                                    @mouseover="
                                        selectResult(index + (results.categories || []).length)
                                    "
                                />
                                <SearchSuggestion
                                    v-for="(creator, index) in results.creators || []"
                                    :key="creator.id"
                                    :link="'search?q=' + creator.name"
                                    :text="creator.name"
                                    :selected="
                                        selectedResultIndex ===
                                        index +
                                            (results.categories || []).length +
                                            (results.streams || []).length
                                    "
                                    @mouseover="
                                        selectResult(
                                            index +
                                                (results.categories || []).length +
                                                (results.streams || []).length
                                        )
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
        </div>
    </div>
</template>
