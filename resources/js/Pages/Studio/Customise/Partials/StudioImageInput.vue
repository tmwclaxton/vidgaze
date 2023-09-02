
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";

const name = 'StudioTextInput';
const textarea = ref(null);
const remaining = ref(0);
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
    label: {
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
});
const resizeTextarea = () => {
    textarea.value.style.height = '5px';
    textarea.value.style.height = textarea.value.scrollHeight + 'px';

}

const originalText = ref('');
onMounted(() => {
    resizeTextarea();
    originalText.value = props.value;
});

const calculateRemaining = () => {
    remaining.value = props.value ? props.maxlength - props.value.length : props.maxlength;
}

watch(() => props.value, () => {
    calculateRemaining();
});

const emits = defineEmits(['update:modelValue','submit']);

const submit = () => {
    if (props.value === originalText.value) {
        return;
    }
    emits('submit');
    originalText.value = props.value;
}

</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">
        <p class="text-xs font-bold" v-text="props.label"></p>
        <textarea ref="textarea"
            :maxlength="props.maxlength"
            @input="resizeTextarea(); $emit('update:modelValue', $event.target.value); calculateRemaining();"
            @focusout="submit"
            @keydown.enter.prevent="() => { if (props.enterSubmit) { submit(); } else { $refs.textarea.value += '\n'; resizeTextarea();} }"
            :value="props.value"
            :name="props.name"
            :placeholder="props.placeholder"
            autocomplete="off"
            class="mt-1 w-full block p-1 resize-none text-sm bg-transparent
            without-ring border-t-0 border-x-0 border-b-1 border-zinc-300 focus:border-zinc-500 dark:border-zinc-700 focus:dark:border-zinc-500 focus:border-b "/>
        <p id="remaining" class="mt-1 text-right text-xs">
            <span v-text="remaining"></span> / <span v-text="props.maxlength"></span>
        </p>
    </consistent-content-holder>
</template>



