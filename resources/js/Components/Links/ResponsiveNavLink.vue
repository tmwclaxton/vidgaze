<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {useNavStore} from "@/Stores/NavStore";
const navStore = useNavStore();
const props = defineProps({
    'href': {
        type: String,
        required: false,
        default: '#'
    },
    'active': {
        type: Boolean,
        required: false,
        default: false
    },
    'span': {
        type: Boolean,
        required: false,
        default: false
    }
});


const classes = computed(() => {
    let classString = '';
    if (props.active) {
        classString =
            'group border-transparent bg-white/[0.1] text-white shadow-sm ring-1 ring-white/[0.08] dark:bg-white/[0.08] dark:ring-white/[0.06] ';
    } else {
        classString =
            'group border-transparent text-zinc-400 hover:border-transparent hover:bg-white/[0.06] dark:hover:bg-white/[0.06] ';
    }
    if (navStore.getNavigationDropdown()) {
        classString +=
            'mr-2 w-full min-w-0 flex-nowrap flex-row gap-x-3 px-3 text-base sm:mr-2 sm:px-3.5 ';
    } else {
        classString +=
            'w-full min-w-0 gap-2 px-2.5 text-sm sm:flex-col sm:items-center sm:justify-center sm:gap-1.5 sm:px-2 sm:text-center sm:text-[11px] ';
    }
    classString +=
        'flex items-center rounded-lg py-2 text-left font-medium transition-colors duration-150 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white/25 sm:py-2 ';
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
    <Link v-if="!span" :href="href" :class="classes" @click="closeNavigation" class=" cursor-pointer">
        <slot />
    </Link>
    <span v-else :class="classes" class="  cursor-pointer ">
        <slot />
    </span>
</template>
