<script setup>
import {ref} from "vue";

const name = 'SuggestionsScreen';

const suggestions = ref([]);

// grab suggestions using axios and ziggy
axios.get('/api/suggestions')
    .then(response => {
        suggestions.value = response.data;
    })
    .catch(error => {
        console.log(error);
    }


</script>
<template>

    <div id="suggestionsScreen"
         class="m-10 grid grid-cols-3 gap-5 h-max mt-10 sm:my-auto">
        <!--@foreach($endScreenSuggestions as $suggestion)-->
        <!--<x-watch-end-screen-suggestion :video="$suggestion"/>-->
        <!--@endforeach-->
    </div>

</template>


