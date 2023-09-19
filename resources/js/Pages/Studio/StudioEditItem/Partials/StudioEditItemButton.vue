<script setup>
const props = defineProps({
    currentTab: {
        type: String,
        required: true
    },
    tab: {
        type: String,
        required: true
    },
});
const emits = ['changePage'];
</script>

<template>
    <div class="flex flex-row  w-full">
        <button @click="$emit('changePage')"
                class="inline-block py-2 text-lg font-bold capitalize border-l-4 w-full rounded"
                :class="[(currentTab === tab) ? 'bg-zinc-100 dark:bg-zinc-900 dark:border-zinc-400 border-zinc-600 text-zinc-700 dark:text-zinc-200 dark:hover:text-zinc-300  hover:text-zinc-600 '
                : 'border-transparent hover:border-zinc-200 dark:hover:border-transparent dark:text-zinc-400 dark:hover:text-zinc-300  hover:text-zinc-600']"
                v-text="tab"/>
    </div>
</template>
