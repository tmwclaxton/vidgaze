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
        class="group h-10 inline-flex items-center gap-x-3 text-center px-4 py-2
        bg-zinc-200 dark:bg-zinc-900  hover:bg-zinc-300  dark:hover:bg-zinc-800 dark:border border-zinc-800
        rounded-lg font-semibold text-xs text-zinc-700 dark:text-zinc-300  shadow-sm

         focus:outline-none disabled:opacity-25 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>
