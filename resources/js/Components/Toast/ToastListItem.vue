<script setup>
import {onMounted} from "vue";

import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

const props = defineProps({
    message: String,
    type: {
        type: String,
        default: "normal"
    },
    duration: {
        type: Number,
        default: 2000
    }
});

onMounted(() => {
    setTimeout(() => emit("remove"), props.duration);
});

const emit = defineEmits(["remove"]);
</script>
<template>
    <div
        class="flex items-center rounded-lg p-4 shadow-md bg-white text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400"
        role="alert"
    >
        <div
            class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg pl-0.5
             "
            :class="{
          'bg-blue-200 text-blue-700 dark:bg-blue-800 dark:text-blue-100': props.type === 'normal',
          'bg-green-200 text-zinc-900 dark:bg-green-900 dark:text-white': props.type === 'success',
          'bg-red-200 text-red-900 dark:bg-red-800 dark:text-red-100': props.type === 'error'
        }"

        >
            <font-awesome-icon v-if="props.type === 'normal'" class="   aspect-square  mx-auto"
                               :icon="['fas', 'message']" />
            <font-awesome-icon v-if="props.type === 'success'" class="   aspect-square  mx-auto"
                               :icon="['fas', 'check']" />
            <font-awesome-icon v-if="props.type === 'error'" class="    aspect-square  mx-auto"
                               :icon="['fas', 'triangle-exclamation']" />
         </div>
        <div class="ml-3 text-sm font-normal">{{ props.message }}</div>
        <button
            @click="emit('remove')"
            type="button"
            class="-mx-1.5 -my-1.5 ml-auto inline-flex h-8 w-8 rounded-lg bg-transparent p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900   dark:text-zinc-500 dark:hover:bg-zinc-800 dark:hover:text-white"
            data-dismiss-target="#toast-default"
            aria-label="Close"
        >
            <span class="sr-only">Close</span>
            <font-awesome-icon class=" h-5 w-5 "  :icon="['fas', 'times']" />
        </button>
    </div>
</template>
