<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center text-center px-4 py-2 bg-zinc-200 dark:bg-zinc-700  rounded-md font-semibold text-xs text-zinc-700 dark:text-zinc-300  shadow-sm hover:bg-zinc-300 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 disabled:opacity-25 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>
