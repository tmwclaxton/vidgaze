<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['href', 'active','span']);

const classes = computed(() =>
    props.active
        ? 'w-max min-w-32 w-min rounded text-md block w-full pl-3 pr-4 py-2 border-l-4  border-zinc-600 text-left text-base font-medium  text-zinc-300  bg-zinc-900  focus:outline-none focus:text-zinc-200 focus:bg-zinc-900 focus:border-zinc-300 transition duration-150 ease-in-out'
        : 'w-max min-w-32 rounded text-md block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-white  hover:text-zinc-200  hover:bg-zinc-800  hover:border-zinc-600 focus:outline-none  focus:text-zinc-200  focus:bg-zinc-700  focus:border-zinc-600 transition duration-150 ease-in-out'
);
</script>

<template>
    <Link v-if="!span" :href="href" :class="classes">
        <slot />
    </Link>
    <span v-else :class="classes" class="w-min-52 select-none cursor-pointer ">
        <slot />
    </span>
</template>
