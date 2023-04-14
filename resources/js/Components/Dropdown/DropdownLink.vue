<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link
        class="block w-full px-4 py-2 mt-0.5 text-left text-sm leading-5 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800
        focus:outline-none focus:bg-zinc-200 dark:focus:bg-zinc-800 transition duration-150 ease-in-out rounded-md"
    >
        <slot />
    </Link>
</template>
