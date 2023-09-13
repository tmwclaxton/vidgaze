
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";


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
    errors: {
        type: Object,
        default: null
    }
});

const emits = defineEmits(['update:modelValue']);

const platformsLocal = ref(props.platforms);

</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">

            <p class="text-xs font-bold">Primary source</p>
            <p class="text-xs mx-1 my-1">This is the source shown on <span class="font-bold">VidGaze</span> </p>

        <div>
            <div class="mt-2  mx-1  ">
                <select name="platforms" class=" text-sm font-semibold mx-2 my-2 without-ring">
                    <option class="hidden" value="default" selected v-text="preferred_source"/>
                    <option v-for="source in sources" :value="source.source_name" v-text="source.source_name"/>
                </select>
            </div>
            <InputError class="mt-2" :message="errors.platforms ? errors.platforms[0] : null"/>
        </div>
    </consistent-content-holder>
</template>



