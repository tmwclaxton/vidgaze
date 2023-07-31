<template>
    <div class="flex items-center w-full">
        <input
            type="date"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-500 dark:focus:border-blue-600
      border focus:ring-2 border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm"
        />
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const { modelValue } = defineProps([
    'modelValue',
]);

const emit = defineEmits();
</script>

<style scoped>
/* Custom styles to change the calendar icon color to white in dark mode with a transition effect */
.dark input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    transition: filter 0.3s ease; /* Add a 0.3s transition to the filter property */
}

/* Set the initial state to black (inverted) for the calendar icon */
input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(0);
}
</style>
