<template>
    <Link
          v-bind:href="link" class="search-suggestion select-none" >
        <div class=" overflow-x-hidden bg-zinc-800 hover:bg-zinc-700 rounded-md ease-in-out duration-400 transition shadow-md shadow">
            <div scope="row" class="h-8 overflow-y-hidden flex px-3 py-2 text-base font-medium text text-white ">
                <div class="flex-shrink-0 w-4 mr-3 my-auto flex flex-col justify-center items-center">
                    <SearchIcon class="w-4 h-4"/>
                </div>

                <div class="line-clamp-1  overflow-y-hidden  flex flex-col justify-center items-center ">
                    <p v-text="text" class="font-semibold text-left leading-4 my-auto  line-clamp-1 break-words "/>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/inertia-vue3'
const { text, link } = defineProps({
    text: {
        type: String,
        required: true
    },
    link: {
        type: String,
        required: true
    }
})

</script>

<style scoped>

</style>
