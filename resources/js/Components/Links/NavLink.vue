<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps(['href', 'active']);

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 border-cyan-400 leading-5 text-cyan-100 drop-shadow-[0_0_12px_rgba(34,211,238,0.35)] focus:outline-none focus:border-cyan-300 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 border-transparent leading-5 text-white/90 hover:text-cyan-100 hover:border-cyan-400/50 hover:drop-shadow-[0_0_10px_rgba(34,211,238,0.25)] focus:outline-none focus:border-cyan-400/40 transition duration-150 ease-in-out'
);
</script>

<template>
    <!--<span class="hidden border-b-2"></span>-->
    <Link :href="href"  :class="'pb-1  pt-1 border-b-2  ' + classes" >
        <slot />
    </Link>
</template>
