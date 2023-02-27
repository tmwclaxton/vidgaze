<template>
    <Link tabindex="0" class="search-suggestion" v-bind:href="link" >
        <div class="overflow-x-hidden bg-white dark:bg-zinc-900 dark:border-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-800">
            <div class="h-8 overflow-y-hidden flex px-3 py-2 text-base font-medium text dark:textDark dark:text-white ">
                <div class="flex-shrink-0 w-4 mr-3 text dark:textDark my-auto flex flex-col justify-center items-center">
                    <!--<x-icon name="search"></x-icon>-->
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
