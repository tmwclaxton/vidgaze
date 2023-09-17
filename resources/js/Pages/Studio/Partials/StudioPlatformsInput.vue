
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
    platforms: {
        type: Array,
        default: ''
    },
    uploadable_platforms: {
        type: Array,
        default: ''
    },
    error_message: {
        type: String,
        default: null
    }
});

const emits = defineEmits(['update:modelValue']);

const submit = () => {
    // deselect
    // textarea.value.blur();
    emits('update:modelValue');
}

const platformsLocal = ref(props.platforms || []);

watch(platformsLocal, () => {
    console.log('platformsLocal changed');
    emits('update:modelValue', platformsLocal.value);
});

// grab value from each item in uploadable_platforms
const uploadable_platforms_value = computed(() => {
    let platforms = [];
    props.uploadable_platforms.forEach((platform) => {
        platforms.push(platform.value);
    });
    return platforms;
});

</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">

            <p class="text-xs font-bold">Platforms</p>
            <p class="text-xs mx-1 my-1">These are the platforms where your video will be published to</p>


        <div class="ml-2 space-y-2 select-none">
            <div v-if="uploadable_platforms_value.includes('youtube')"
                class="flex flex-row gap-2 items-center">
                <input type="checkbox" id="youtube" value="youtube" v-model="platformsLocal"
                       :checked="platformsLocal.includes('youtube')"
                       class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600 hover:dark:bg-zinc-700 focus:dark:bg-zinc-700">
                <label for="youtube" class="flex flex-row gap-x-2 align-middle items-center">
                    <YouTubeIcon class="w-6 h-6 "/>
                    <p class="font-semibold text-sm">YouTube</p>
                </label>
            </div>
            <div v-if="uploadable_platforms_value.includes('dailymotion')"
                 class="flex flex-row gap-2 items-center">
                <input type="checkbox" id="dailymotion" value="dailymotion" v-model="platformsLocal"
                          :checked="platformsLocal.includes('dailymotion')"
                       class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600 hover:dark:bg-zinc-700 focus:dark:bg-zinc-700">
                <label for="dailymotion" class="flex flex-row gap-x-2  align-middle items-center">
                    <DailyMotionIcon class="w-6 h-6"/>
                    <p class="font-semibold text-sm">Dailymotion</p>
                </label>
            </div>
            <div v-if="uploadable_platforms_value.includes('vimeo')"
                 class="flex flex-row gap-2 items-center">
                <input type="checkbox" id="vimeo" value="vimeo" v-model="platformsLocal"
                        :checked="platformsLocal.includes('vimeo')"
                       class="w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600 hover:dark:bg-zinc-700 focus:dark:bg-zinc-700">
                <label for="vimeo" class="flex flex-row gap-x-2 align-middle items-center">
                    <VimeoIcon class="w-6 h-6"/>
                    <p class="font-semibold text-sm">Vimeo</p>
                </label>
            </div>
        </div>


        <InputError v-if="props.error_message" class="mt-2" :message="props.error_message"/>

    </consistent-content-holder>
</template>



