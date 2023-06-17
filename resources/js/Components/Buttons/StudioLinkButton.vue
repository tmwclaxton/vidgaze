<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    platform: {
        type: String,
        required: true
    },
    text: {
        type: String,
        required: true
    },
    buttonClasses: {
        type: String,
        required: false,
        default: ""
    }
});

</script>
<template>
    <a :href="'/studio/login/'+platform" class="w-max ">
        <button type="button" :class="buttonClasses" class="h-full w-max flex flex-row inline-flex items-center gap-2
         dark:bg-vidgaze-blue dark:hover:bg-zinc-800 dark:text-zinc-200 text-zinc-900 text-sm font-medium rounded-lg px-5 py-2.5 text-center ">
            <slot></slot>
            <p class="break-words w-full text-left capitalize" v-text="text"/>
        </button>
    </a>
</template>

