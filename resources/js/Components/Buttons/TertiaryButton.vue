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
        bg-zinc-200 dark:bg-zinc-900 hover:bg-zinc-300 dark:hover:bg-zinc-800 border border-zinc-300 dark:border-zinc-700
        rounded-lg font-semibold text-xs text-zinc-700 dark:text-zinc-300 shadow-sm
        hover:border-cyan-400/40 dark:hover:border-cyan-500/30 hover:shadow-[0_0_14px_-6px_rgba(34,211,238,0.18)]
         focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/35 disabled:opacity-25 transition ease-in-out duration-150"
    >
        <slot />
    </button>
</template>
