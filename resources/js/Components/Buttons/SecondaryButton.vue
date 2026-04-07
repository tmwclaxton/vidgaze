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
        class="inline-flex items-center px-4 py-2 bg-white dark:bg-zinc-800 rounded-md border border-zinc-200/80 dark:border-zinc-600 font-semibold text-xs text-zinc-700 dark:text-zinc-300 uppercase tracking-widest shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-700 hover:border-cyan-400/45 dark:hover:border-cyan-500/35 focus:outline-none focus:ring-2 focus:ring-cyan-400/50 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 disabled:opacity-25 transition ease-in-out duration-150 hover:shadow-[0_0_18px_-8px_rgba(34,211,238,0.22)]"
    >
        <slot />
    </button>
</template>
