<script setup>
import {onMounted, ref} from "vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import {usePlayerStore} from "@/Stores/PlayerStore";
import EndScreenSuggestion from "@/Pages/Viewer/Watch/Partials/SuggestionsScreen/EndScreenSuggestion.vue";
const contentRoutesStore = useContentRoutesStore();
const playerStore = usePlayerStore();
const name = 'SuggestionsScreen';

let suggestions = ref([]);

// grab suggestions using axios and ziggy
onMounted(async () => {
    await contentRoutesStore.getVideos("random", 6).then((response) => {
            suggestions.value = response;
        });
});
</script>
<template>

    <div id="suggestionsScreen"
         class="m-10 grid grid-cols-3 gap-5 h-max mt-10 sm:my-auto max-w-6xl mx-auto px-10">
        <div v-for="suggestion in suggestions" :key="suggestion.id" class="">
           <EndScreenSuggestion :video="suggestion" />
        </div>
    </div>

</template>


