<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 border-indigo-500 dark:border-indigo-600  leading-5 text-white focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 border-transparent   leading-5 text-white hover:text-zinc-300 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-500 focus:outline-none   dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out'
);
</script>

<template>
    <!--<span class="hidden border-b-2"></span>-->
    <Link :href="href"  :class="'pb-1  pt-1 border-b-2  ' + classes" >
        <slot />
    </Link>
</template>
