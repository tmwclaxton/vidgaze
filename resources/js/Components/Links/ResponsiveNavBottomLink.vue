<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import {useNavStore} from "@/Stores/NavStore";
const navStore = useNavStore();

const props = defineProps(['href', 'active','span']);


const classes = computed(() => {
    let classString = '';
    if (props.active) {
        classString =
            'border-transparent bg-white/[0.1] text-white ring-1 ring-white/[0.06] dark:bg-white/[0.08] ';
    } else {
        classString =
            'border-transparent text-zinc-400 hover:bg-white/[0.06] hover:text-white dark:hover:bg-white/[0.06] ';
    }
    if (navStore.getNavigationDropdown()) {
        classString += 'flex-row justify-center px-2 py-2 text-center text-xs ';
    } else {
        classString +=
            'w-full min-w-0 justify-center gap-2 px-2 py-2 text-center sm:flex-col sm:items-center sm:justify-center sm:gap-1.5 sm:text-[10px] ';
    }
    classString +=
        'group flex w-full items-center rounded-md font-medium transition-colors duration-150 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/20 ';
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
