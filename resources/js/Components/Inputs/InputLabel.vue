<script setup>
defineProps(['value']);
</script>

<template>
    <label class="block font-semibold text-sm text-gray-700 dark:text-gray-300 select-none">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
