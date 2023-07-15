<script setup>
import {onMounted, ref} from "vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import {usePlayerStore} from "@/Stores/PlayerStore";
const contentRoutesStore = useContentRoutesStore();
const playerStore = usePlayerStore();
const name = 'SuggestionsScreen';

let suggestions = ref([]);

// grab suggestions using axios and ziggy
onMounted(async () => {
    if (playerStore.endScreen ) {
        await contentRoutesStore.getVideos("popular", 6).then((response) => {
            suggestions.value = response;
        });
    }
});
</script>
<template>

    <div id="suggestionsScreen"
         class="m-10 grid grid-cols-3 gap-5 h-max mt-10 sm:my-auto">
        <!--@foreach($endScreenSuggestions as $suggestion)-->
        <!--<x-watch-end-screen-suggestion :video="$suggestion"/>-->
        <!--@endforeach-->
    </div>

</template>


