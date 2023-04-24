<template>
    <div class="search-suggestion select-none" >
        <div class=" overflow-x-hidden  rounded-md ease-in-out duration-400 transition shadow-md shadow"
        :class="{'bg-zinc-700': selected, 'bg-zinc-800' : !selected}">
            <div scope="row" class="h-8 overflow-y-hidden flex px-3 py-2 text-base font-medium text text-white ">
                <div class="flex-shrink-0 w-4 mr-3 my-auto flex flex-col justify-center items-center">
                    <SearchIcon class="w-4 h-4"/>
                </div>
                <div class="line-clamp-1  overflow-y-hidden  flex flex-col justify-center items-center ">
                    <p v-text="text" class="font-semibold text-left leading-4 my-auto  line-clamp-1 break-words "/>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/inertia-vue3'
import SearchIcon from '~/images/icons/search.svg';
const { text, link } = defineProps({
    text: {
        type: String,
        required: true
    },
    selected: {
        type: Boolean,
        required: true
    }
})

</script>

<style scoped>

</style>
