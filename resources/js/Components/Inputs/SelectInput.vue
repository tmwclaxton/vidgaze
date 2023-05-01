<template>
    <div class="relative">
        <select
            v-bind="$attrs"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="block w-full px-4 py-1 text-base font-semibold text-zinc-700 dark:text-zinc-200
           rounded-lg appearance-none
            focus:outline-none focus:shadow-outline-blue focus:border-blue-300
            bg-white dark:bg-zinc-800 placeholder-zinc-400 dark:placeholder-zinc-500  border border-zinc-300 dark:border-zinc-600"
        >
            <option v-for="option in options" :key="option.value" :value="option.value" class="py-1">
                {{ option.label }}
            </option>
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
            <svg class="w-4 h-4 text-zinc-400 fill-current" viewBox="0 0 20 20">
                <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" />
            </svg>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

defineProps({
    modelValue: {
        type: String,
        required: true
    },
    options: {
        type: Array,
        required: true
    }
});
</script>

<style scoped>
select:focus {
    outline: none;
}

@media (prefers-color-scheme: dark) {
    .dark select {
        background-color: #374151;
        color: #D1D5DB;
        border-color: #4B5563;
    }

    .dark select option {
        background-color: #374151;
        color: #D1D5DB;
    }
}
</style>
