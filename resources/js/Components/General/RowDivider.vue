<template>
    <hr v-if="!text" class="flex my-8 border-2 border-zinc-100 dark:border-zinc-800" />
    <div v-else class="flex flex-row items-center justify-center gap-2 my-8 pointer-events-none px-2 ">
        <hr class="pointer-events-none flex-grow border-2 border-zinc-100 dark:border-zinc-800 rounded-full" />

        <div class="pointer-events-auto flex flex-row-reverse gap-x-2 align-middle items-center cursor-pointer px-2">
            <slot></slot>
            <p class="  text-lg font-bold  ">{{ text }}</p>
        </div>

        <hr class="pointer-events-none flex-grow border-2 border-zinc-100 dark:border-zinc-800 rounded-full" />
    </div>
</template>

<script setup>
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const name = 'RowDivider';

const props = defineProps({
    text: {
        type: String,
        default: null
    }
});
</script>
