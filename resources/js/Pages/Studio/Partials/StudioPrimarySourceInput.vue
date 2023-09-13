
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";

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

</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">

            <p class="text-xs font-bold">Primary source</p>
            <p class="text-xs mx-1 my-1">This is the source shown on <span class="font-bold">VidGaze</span> </p>

            <div class="mt-2  mx-1  ">
                <p class="text-xs font-bold">Select Primary Source</p>
                <select name="platforms" class=" text-sm font-semibold mx-2 my-2 without-ring">
                    <option class="hidden" value="default" selected v-text="preferred_source"/>
                    <option v-for="source in sources" :value="source.source_name" v-text="source.source_name"/>
                </select>
            </div>


        <InputError v-if="props.error_message" class="mt-2" :message="props.error_message"/>

    </consistent-content-holder>
</template>



