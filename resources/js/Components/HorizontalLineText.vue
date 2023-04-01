<template>
    <div class="flex items-center align-middle py-4">
        <!-- The left line -->
        <div class="flex-grow h-0.5 bg-gray-400 dark:bg-zinc-800"></div>

        <!-- Your text here -->
        <p class="  text-xl text-gray-500 dark:text-zinc-500 mb-1 px-4    ">{{ text }}</p>

        <!-- The right line -->
        <div class="flex-grow h-0.5 bg-gray-400 dark:bg-zinc-800"></div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    text: {
        type: String,
        required: true,
    },
});
</script>
