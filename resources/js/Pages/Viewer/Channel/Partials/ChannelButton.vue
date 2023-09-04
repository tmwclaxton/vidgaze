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
    <div class="flex flex-row  ">
        <button @click="$emit('changePage')"
                class="inline-block p-4 px-6 text-lg font-bold capitalize border-b-2"
                :class="[(currentTab === tab) ? 'dark:border-zinc-400 border-zinc-600 text-zinc-700 dark:text-zinc-200 dark:hover:text-zinc-300  hover:text-zinc-600 '
                : 'border-transparent hover:border-zinc-200 dark:hover:border-transparent dark:text-zinc-400 dark:hover:text-zinc-300  hover:text-zinc-600']"
                v-text="tab"/>
    </div>
</template>
