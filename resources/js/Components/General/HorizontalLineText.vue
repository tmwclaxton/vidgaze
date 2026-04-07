<template>
    <div class="flex items-center align-middle py-2">
        <!-- The left line -->
        <div class="h-0.5 flex-grow rounded-full bg-gradient-to-r from-transparent via-cyan-400/45 to-transparent dark:via-fuchsia-400/35"></div>

        <!-- Your text here -->
        <p
            class="mb-1 bg-gradient-to-r from-cyan-600 to-fuchsia-600 bg-clip-text px-4 text-xl font-semibold text-transparent dark:from-cyan-400 dark:to-fuchsia-400"
        >
            {{ text }}
        </p>

        <!-- The right line -->
        <div class="h-0.5 flex-grow rounded-full bg-gradient-to-r from-transparent via-fuchsia-400/45 to-transparent dark:via-cyan-400/35"></div>
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
