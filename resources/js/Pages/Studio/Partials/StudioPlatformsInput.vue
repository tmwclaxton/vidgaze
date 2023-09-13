
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";

import YouTubeIcon from '#icons/youtube.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';
const name = 'StudioMadeForKidsInput';
const props = defineProps({
    preferred_source: {
        type: String,
        default: ''
    },
    sources: {
        type: Array,
        default: ''
    },
    error_message: {
        type: String,
        default: null
    }
});

const emits = defineEmits(['update:modelValue','submit']);

const submit = () => {
    // deselect
    // textarea.value.blur();
    emits('update:modelValue');
}

const platformsLocal = ref(props.platforms);
</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">

            <p class="text-xs font-bold">Platforms</p>
            <p class="text-xs mx-1 my-1">These are the platforms where your video will be published to</p>


        <div class="ml-2 space-y-2">
            <div class="flex items-center">
                <input type="checkbox" id="youtube" value="youtube" v-model="platformsLocal" class="mr-2">
                <YouTubeIcon class="w-6 h-6 mr-2"/>
                <label for="youtube">YouTube</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="dailymotion" value="dailymotion" v-model="platformsLocal" class="mr-2">
                <DailyMotionIcon class="w-6 h-6 mr-2"/>
                <label for="dailymotion">Dailymotion</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="vimeo" value="vimeo" v-model="platformsLocal" class="mr-2">
                <VimeoIcon class="w-6 h-6 mr-2"/>
                <label for="vimeo">Vimeo</label>
            </div>
        </div>


        <InputError v-if="props.error_message" class="mt-2" :message="props.error_message"/>

    </consistent-content-holder>
</template>



