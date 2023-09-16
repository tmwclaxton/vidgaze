<template>
    <select
        required
        :value="modelValue"
        :name="name"
        :id="id"
        class="without-ring"
        :class="inputClasses"
        @change="$emit('update:modelValue', $event.target.value)"
        ref="input"
    >
        <option v-for="item in items" :value="item.value" :key="item.value">
            {{ item.name }}
        </option>
    </select>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: null
    },
    name: {
        type: String,
        default: null
    },
    id: {
        type: String,
        default: null
    },
    items: {
        type: Array,
        default: null
    }
});

const inputClasses = computed(() => {
    return `without-ring dark:bg-zinc-900 dark:text-zinc-300 focus:outline-none border focus:ring-1 border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm`;
});

const emit = defineEmits();
</script>
