<template>
    <select
        required
        :value="modelValue"
        :name="name"
        :id="id"
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

const { modelValue, name, id, items } = defineProps([
    'modelValue',
    'name',
    'id',
    'items',
]);

const inputClasses = computed(() => {
    return `dark:bg-gray-900 dark:text-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-500 dark:focus:border-blue-600 border focus:ring-2 border-zinc-300 dark:border-zinc-600 rounded-lg shadow-sm`;
});

const emit = defineEmits();
</script>
