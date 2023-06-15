<template>
    <hr v-if="!text" class="flex my-8 border-2 border-zinc-100 dark:border-zinc-800" />
    <div v-else class="flex flex-row items-center justify-center gap-2 my-8">
        <hr class="flex-grow border-2 border-zinc-100 dark:border-zinc-800" />

        <div class="flex flex-row-reverse gap-x-2 align-middle items-center cursor-pointer">
            <slot></slot>
            <p class="  text-lg font-bold  ">{{ text }}</p>
        </div>

        <hr class="flex-grow border-2 border-zinc-100 dark:border-zinc-800" />
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
