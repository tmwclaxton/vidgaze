<template>
    <hr
        v-if="!text"
        class="my-8 h-0.5 w-full flex border-0 bg-gradient-to-r from-transparent via-cyan-400/35 to-transparent dark:via-fuchsia-400/30 rounded-full shadow-[0_0_12px_rgba(34,211,238,0.12)]"
    />
    <div v-else class="flex flex-row items-center justify-center gap-2 my-8 pointer-events-none px-2 ">
        <hr class="pointer-events-none h-0.5 flex-grow border-0 bg-gradient-to-r from-transparent to-cyan-400/30 dark:to-fuchsia-400/25 rounded-full" />

        <div class="pointer-events-auto flex flex-row-reverse gap-x-2 align-middle items-center cursor-pointer px-2">
            <slot></slot>
            <p
                class="bg-gradient-to-r from-cyan-600 to-fuchsia-600 bg-clip-text text-base font-semibold text-transparent dark:from-cyan-400 dark:to-fuchsia-400 sm:text-lg"
            >
                {{ text }}
            </p>
        </div>

        <hr class="pointer-events-none h-0.5 flex-grow border-0 bg-gradient-to-l from-transparent to-cyan-400/30 dark:to-fuchsia-400/25 rounded-full" />
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
