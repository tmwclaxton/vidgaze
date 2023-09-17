
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

// based on props.platforms and props.uploadable_platforms
const platforms = computed(() => {
    let platforms = [];
    props.uploadable_platforms.forEach((platform) => {
        if (props.platforms.includes(platform.value)) {
            platforms.push(platform);
        }
    });
    return platforms;
});

const preferred_source_local = ref(props.preferred_source);

// watch if props.platforms doesn't have preferred_source
watch(() => props.platforms, (value) => {
    check();
});

onMounted(() => {
    check();
});

const check = () => {
    if (!props.platforms.includes(preferred_source_local.value) || preferred_source_local.value === '') {
        preferred_source_local.value = platforms.value[0].value;
        emits('update:modelValue', platforms.value[0].value);
    }
}
</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">

            <p class="text-xs font-bold">Primary source</p>
            <p class="text-xs mx-1 my-1">This is the source shown on <span class="font-bold">VidGaze</span> </p>

        <div>
            <div class="mt-2  mx-1  ">
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



