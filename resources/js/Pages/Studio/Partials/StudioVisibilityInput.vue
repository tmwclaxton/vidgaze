

<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import DateInput from "@/Components/Inputs/DateInput.vue";

import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
const name = 'StudioVisibilityInput';
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

const emits = defineEmits(['update:modelVisibility','update:modelPublishTime']);


const visibility = ref(props.value);
const publishing_time = ref(props.publish_time);
watch (() => props.value, (value) => {
    visibility.value = value;
});
watch (() => props.publish_time, (value) => {
    publishing_time.value = value;
});
watch( () => publishing_time.value, (value) => {
    // 2023-09-30T07:57:00.000Z -> 2023-09-30T07:57:00
    var date = new Date(publishing_time.value);
    var date_string = date.toISOString();
    console.log(date_string);
    emits('update:modelPublishTime', date_string);
});
</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">
        <div>
            <p class="text-xs font-bold mb-1">Visibility (Required)</p>

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
                        :checked="visibility === 'private'"
                        @click="$emit('update:modelVisibility', 'private')"
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
                        name="visibility"
                        value="unlisted"
                        class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                        v-model="visibility"
                        :checked="visibility === 'unlisted'"
                        @click="$emit('update:modelVisibility', 'unlisted')"
                    >
                    <label for="unlisted" class="flex flex-col ml-2 cursor-pointer select-none">
                        <span class="font-semibold text-sm font-medium text-zinc-900 dark:text-zinc-200">Unlisted</span>
                        <span class="text-xs">Anyone with the video link can watch your video</span>
                    </label>
                </div>
                <div class="flex items-center">
                    <input
                        id="public"
                        type="radio"
                        name="visibility"
                        value="public"
                        class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                        v-model="visibility"
                        :checked="visibility === 'public'"
                        @click="$emit('update:modelVisibility', 'public')"
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
                            :checked="visibility === 'scheduled'"
                            @click="$emit('update:modelVisibility', 'scheduled')"
                        >
                        <label
                            for="scheduled"
                            class="flex flex-col ml-2 cursor-pointer select-none">
                            <span class="cursor-pointer font-semibold text-sm font-medium text-zinc-900 dark:text-zinc-200">Schedule</span>
                            <span class="cursor-pointer text-xs">Select a date to make your video  <span class="font-bold">public</span></span>
                        </label>
                    </div>
                    <!--<DateInput class="mt-2" v-if="visibility === 'scheduled'" v-model="publishing_time"/>-->
                    <div class="w-64">
                        <VueDatePicker class="ml-6 mt-2" v-if="visibility === 'scheduled'" v-model="publishing_time" minutes-increment="1"
                                       :action-row="{ showNow: true }" now-button-label="Current" utc
                        />
                    </div>
                    <InputError class="ml-6 mt-2" :message="errors.publish_time ? errors.publish_time[0] : null"/>
                </div>
            </div>
            <InputError class="mt-2" :message="errors.visibility ? errors.visibility[0] : null"/>
        </div>

    </consistent-content-holder>
</template>



