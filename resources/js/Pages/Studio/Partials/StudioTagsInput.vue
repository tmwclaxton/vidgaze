
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import TagInput from "@/Components/Inputs/TagInput.vue";

const name = 'StudioTagsInput';
const props = defineProps({
    maxlength: {
        type: Number,
        default: 50
    },
    value: {
        type: String,
        default: ''
    },
    placeholder: {
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
    error_message: {
        type: String,
        default: null

    }
});



const emits = defineEmits(['update:modelValue','submit']);

const submit = () => {
    // deselect
    // textarea.value.blur();
    emits('submit');
}

const enter = () => {

}

</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">
        <p class="text-xs font-bold" v-text="props.label"></p>
        <InputLabel class="mb-1" for="tags" value="Tags"/>
        <TagInput v-model="props.value" :key="key"/>
        <InputError v-if="props.error_message" class="mt-2" :message="props.error_message"/>

    </consistent-content-holder>
</template>



