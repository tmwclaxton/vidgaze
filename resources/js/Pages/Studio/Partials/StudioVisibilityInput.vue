

<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import DateInput from "@/Components/Inputs/DateInput.vue";

const name = 'StudioMadeForKidsInput';
const props = defineProps({
    value: {
        type: String,
        default: ''
    },
    publish_time: {
        type: String,
        default: ''
    },
    for: {
        type: String,
        default: ''
    },
    name: {
        type: String,
        default: ''
    },
    enterSubmit: {
        type: Boolean,
        default: true
    },
    errors: {
        type: Object,
        default: null
    }
});

const emits = defineEmits(['update:modelValue','submit']);

const submit = () => {
    // deselect
    // textarea.value.blur();
    emits('update:modelValue');
}
const visibility = ref(props.value);
const publishing_time = ref(props.publish_time);
</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">
        <div>
            <InputLabel class="mb-1" for="visibility" value="Visibility (Required)"/>
            <p class="text-xs mx-2 my-1">Choose when to publish and who can see your video </p>

            <div class="ml-2 space-y-2">
                <div class="flex items-center">
                    <input
                        id="private"
                        type="radio"
                        name="visibility"
                        value="private"
                        class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                        v-model="visibility"
                    >
                    <label for="private" class="flex flex-col ml-2 cursor-pointer select-none select-none">
                        <span class="cursor-pointer font-semibold text-sm font-medium text-zinc-900 dark:text-zinc-200">Private</span>
                        <span class="cursor-pointer text-xs">Only you and people you choose can watch your video </span>
                    </label>
                </div>
                <div class="flex items-center">
                    <input
                        id="unlisted"
                        type="radio"
                        value="unlisted"
                        class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                        v-model="visibility"
                        name="visibility"
                    >
                    <label for="unlisted" class="flex flex-col ml-2 cursor-pointer select-none">
                        <span class="font-semibold text-sm font-medium text-zinc-900 dark:text-zinc-200">Unlisted</span>
                        <span class="text-xs">Anyone with the video link can watch your video</span>
                    </label>
                </div>
                <div class="flex items-center">
                    <input
                        required
                        id="public"
                        type="radio"
                        name="visibility"
                        class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                        value="public"
                        v-model="visibility"
                    >
                    <label for="public" class="flex flex-col ml-2 cursor-pointer select-none">
                        <span  class="font-semibold text-sm font-medium text-zinc-900 dark:text-zinc-200">Public</span>
                        <span class="text-xs">Everyone can watch your video</span>
                    </label>
                </div>
                <div>
                    <div class="flex items-center">
                        <input
                            id="scheduled"
                            type="radio"
                            value="scheduled"
                            name="visibility"
                            class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                            v-model="visibility"
                        >
                        <label
                            for="scheduled"
                            class="flex flex-col ml-2 cursor-pointer select-none">
                            <span class="cursor-pointer font-semibold text-sm font-medium text-zinc-900 dark:text-zinc-200">Schedule</span>
                            <span class="cursor-pointer text-xs">Select a date to make your video  <span class="font-bold">public</span></span>
                        </label>
                    </div>
                    <DateInput class="mt-2" v-if="visibility === 'scheduled'" v-model="publishing_time"/>
                    <InputError class="mt-2" :message="errors.publish_time ? errors.publish_time[0] : null"/>
                </div>
            </div>
            <InputError class="mt-2" :message="errors.visibility ? errors.visibility[0] : null"/>
        </div>

    </consistent-content-holder>
</template>



