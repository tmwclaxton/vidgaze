<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import {useNavStore} from "@/Stores/NavStore";
const navStore = useNavStore();

const props = defineProps(['href', 'active','span']);


const classes = computed(() => {
    let classString = '';
    // if on the current page
    if (props.active) {
        classString = 'border-transparent text-white  bg-zinc-900 dark:hover:bg-zinc-800  focus:s hake   ';
    } else {
        classString = 'border-transparent text-zinc-200   hover:text-white  hover:bg-zinc-700   dark:hover:bg-zinc-800     hover:border-zi nc-600 ';
    }
    // if side bar is expanded
    if (navStore.getNavigationDropdown()) {
        classString += '   flex-row text-base    px-1 py-2 ';
    } else {
        classString += 'sm:flex-col  align-middle justify-left sm:gap-2 gap-2 px-3 py-2 w-10 ';
    }
    classString += 'text-center items-center sm:text-xs flex rounded text-md block w-full   text-left  font-medium transition duration-150 ease-in-out ';
    // ... rest of the CSS classes
    return classString;
});
// if screen is mobile sized when the navigation is expanded and the user clicks a link, close the navigation
const closeNavigation = () => {
    if (window.innerWidth < 1200) {
        setTimeout(() => {
            navStore.showingNavigationDropdown = false;
        }, 100 );
    }
};
</script>

<template>
    <Link v-if="!span" :href="href" :class="classes" @click="closeNavigation" class="  cursor-pointer ">
        <slot />
    </Link>
    <span v-else :class="classes" class="  cursor-pointer ">
        <slot />
    </span>
</template>
