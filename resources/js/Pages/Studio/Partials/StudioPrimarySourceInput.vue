
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import {toLower} from "lodash";
import Dropdown from "@/Components/Inputs/Dropdown.vue";


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
    error_message: {
        type: String,
        default: null
    }
});

const emits = defineEmits(['update:modelValue']);

const match_platforms = ref({
    'youtube': 'YouTube',
    'twitch': 'Twitch',
    'facebook': 'Facebook',
    'instagram': 'Instagram',
    'dailymotion': 'Dailymotion',
    'tiktok': 'TikTok',
    'vimeo': 'Vimeo',
});

// based on props.platforms and match_platforms
const platforms = computed(() => {
    var platforms = {};
    for (var i = 0; i < props.platforms.length; i++) {
        var platform = props.platforms[i];
        platforms[platform] = match_platforms.value[platform];
    }
    // now convert into an array of objects of name and value
    var platforms_array = [];
    for (const key in platforms) {
        platforms_array.push({name: platforms[key], value: key});
    }
    return platforms_array;
});

const preferred_source_local = ref(props.preferred_source);
</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">

            <p class="text-xs font-bold">Primary source</p>
            <p class="text-xs mx-1 my-1">This is the source shown on <span class="font-bold">VidGaze</span> </p>

        <div>
            <div class="mt-2  mx-1  ">
                <!--<select name="platforms" class=" text-sm font-semibold mx-2 my-2 without-ring"-->
                <!--        @change="$emit('update:modelValue', $event.target.value)">-->
                <!--    &lt;!&ndash;value should be lowercase&ndash;&gt;-->
                <!--    &lt;!&ndash;<option class="hidden" selected v-text="preferred_source" :value="preferred_source"/>&ndash;&gt;-->
                <!--    <option v-for="(value, key) in platforms" :value="key" v-text="value" :selected="key === preferred_source"/>-->
                <!--</select>-->
                <Dropdown
                    @update:modelValue="emits('update:modelValue', $event)"
                    v-model="preferred_source_local"
                    name="preferred_source"
                    id="preferred_source"
                    :items="platforms"
                    required
                />
            </div>
            <InputError class="mt-2" :message="error_message ? error_message : null"/>
        </div>
    </consistent-content-holder>
</template>



