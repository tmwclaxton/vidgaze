
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";

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
    error_message: {
        type: String,
        default: null

    }
});
const resizeTextarea = () => {
    textarea.value.style.height = '5px';
    textarea.value.style.height = textarea.value.scrollHeight + 'px';
    return Promise.resolve();
}

const originalText = ref('');
onMounted(() => {
    resizeTextarea();
    originalText.value = props.value;
    calculateRemaining();
});

const calculateRemaining = () => {
    remaining.value = props.value ? props.maxlength - props.value.length : props.maxlength;
}

watch(() => props.value, () => {
    calculateRemaining();
});

const emits = defineEmits(['update:modelValue','submit']);

const submit = () => {
    // deselect
    textarea.value.blur();

    if (props.value === originalText.value) {
        return;
    }
    emits('submit');
    originalText.value = props.value;
}

const enter = () => {
    if (props.enterSubmit) {
        submit(); }
    else {
        //at cursor position in text add a new line

        // get cursor position
        const cursorPosition = textarea.value.selectionStart;

        // insert text
        const temp = props.value.substring(0, cursorPosition) + '\n' + props.value.substring(cursorPosition, props.value.length);

        // emit
        emits('update:modelValue', temp);

        resizeTextarea().then(() => {
            // set cursor position
            textarea.value.selectionEnd = cursorPosition + 1;
            textarea.value.selectionStart = cursorPosition + 1;
        });

    }
}

</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">
        <p class="text-xs font-bold" v-text="props.label"></p>
        <textarea ref="textarea"
            :maxlength="props.maxlength"
            @input="resizeTextarea(); $emit('update:modelValue', $event.target.value); calculateRemaining();"
            @focusout="submit"
            @keydown.enter.prevent="enter"
            :value="props.value"
            :name="props.name"
            :placeholder="props.placeholder"
            autocomplete="off"
            class="mt-1 w-full block p-1 resize-none text-sm bg-transparent h-full
            without-ring border-t-0 border-x-0 border-b-1 border-zinc-300 focus:border-zinc-500 dark:border-zinc-700 focus:dark:border-zinc-500 focus:border-b "/>
        <p id="remaining" class="mt-1 text-right text-xs">
            <span v-text="remaining"></span> / <span v-text="props.maxlength"></span>
        </p>
        <InputError v-if="props.error_message" class="mt-2" :message="props.error_message"/>

    </consistent-content-holder>
</template>



