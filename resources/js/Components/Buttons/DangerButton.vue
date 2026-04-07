<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-rose-400/80 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 hover:shadow-[0_0_20px_-8px_rgba(248,113,113,0.45)]"
    >
        <slot />
    </button>
</template>
