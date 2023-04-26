<script setup>
import SearchSuggestion from "./SearchSuggestion";

import {ref, watch} from "vue";
import {Inertia} from "@inertiajs/inertia";
import axios from "axios";
let search = ref('');
let results = ref([]);

watch(search, (value) => {
    // Inertia.get('/api/search-bar', {q: value})
    axios.get('/api/search-bar', {params: {q: value}})
        .then(response => {
            results.value = response.data;
        })
        .catch(error => {
            console.log(error);
        });
})

//detect when search is entered
function searchEntered() {
    Inertia.get('/search', {q: search.value});
}
</script>

<template>
    <div class="h-full w-full max-w-2xl mx-auto">
        <div class="flex w-full h-full "  >



            <form @submit.prevent="searchEntered()" class="h-11 p-0 m-0 flex w-full items-center  without-ring rounded-md overflow-hidden" autocomplete="off"
                    style="margin-top: -2px;">

                <label for="voice-search" class="sr-only focus:outline-none">Search</label>
                <div class="relative w-full focus:outline-none">

                    <input v-model="search"
                           type="text" name="q"
                           class="text-sm text-zinc-900 dark:text-white overflow-ellipsis font-semibold  border h-10
                   block w-full border-transparent focus:border-transparent focus:ring-0
                    pr-14 pl-3 p-2.5  generic-background_3 dark:generic-background-dark_2
                     placeholder-zinc-500 dark:placeholder-zinc-400  focus:outline-none "
                           placeholder="Search YouTube, Twitch, Dailymotion, Vimeo and more..." required>

                    <button type="submit" class="focus:outline-none px-3 h-10
                        bg-zinc-200 hover:bg-zinc-300 text-zinc-500 dark:text-zinc-600
                        dark:bg-zinc-900 hover:dark:bg-zinc-900
                        flex absolute inset-y-0 right-0 items-center ">

                        <svg class="w-5 h-5"
                             fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                  clip-rule="evenodd"></path>
                        </svg>

                    </button>
                </div>

            </form>

        </div>

        <div class="   sm:pl-0 max-w-2xl"
             style="@media only screen and (max-width: 640px) {
     margin-left: 1px;
     }">
            <div class="relative w-full pr-11 pl-9 sm:pl-0">
                <div class="relative w-full  ">



                        <table class=" w-full text-sm text-left text-zinc-500 dark:text-zinc-200">
                            <tbody>
                                <!--for each creator or video in results show a search suggestion-->
                                <SearchSuggestion v-for="category in results.categories" :link="'search?q=' + category.name" :text="category.name" :key="category.id"></SearchSuggestion>
                                <SearchSuggestion v-for="stream in results.streams" :link="'search?q=' + stream.title" :text="stream.title" :key="stream.id"></SearchSuggestion>
                                <SearchSuggestion v-for="creator in results.creators" :link="'search?q=' + creator.name" :text="creator.name" :key="creator.id"></SearchSuggestion>
                                <SearchSuggestion v-for="video in results.videos" :link="'search?q=' + video.title" :text="video.title" :key="video.id"></SearchSuggestion>
                                <SearchSuggestion v-for="podcast in results.podcasts" :link="'search?q=' + podcast.title" :text="podcast.title" :key="podcast.id"></SearchSuggestion>
                                <SearchSuggestion v-for="playlist in results.playlists" :link="'search?q=' + playlist.title" :text="playlist.title" :key="playlist.id"></SearchSuggestion>

                            </tbody>
                        </table>

                </div>
            </div>
        </div>



    </div>



</template>


