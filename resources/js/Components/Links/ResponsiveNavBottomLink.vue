<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['href', 'active','span','showingNavigationDropdown']);


const classes = computed(() => {
    let classString = '';
    // if on the current page
    if (props.active) {
        classString = 'border-transparent text-white  bg-zinc-900 dark:hover:bg-zinc-800  focus:s hake   ';
    } else {
        classString = 'border-transparent text-zinc-200   hover:text-white  hover:bg-zinc-700   dark:hover:bg-zinc-800     hover:border-zi nc-600 ';
    }
    // if side bar is expanded
    if (props.showingNavigationDropdown) {
        classString += ' mr-4 flex-row text-base px-4 gap-x-3 ';
    } else {
        classString += 'sm:flex-col  align-middle justify-left sm:gap-2 gap-2 ';
    }
    classString += 'w-10 text-center items-center sm:text-xs px-3 py-2 flex rounded text-md block w-full   text-left  font-medium transition duration-150 ease-in-out ';
    // ... rest of the CSS classes
    return classString;
});

</script>

<template>
    <Link v-if="!span" :href="href" :class="classes">
        <slot />
    </Link>
    <span v-else :class="classes" class=" select-none cursor-pointer ">
        <slot />
    </span>
</template>
