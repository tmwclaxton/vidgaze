
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";

import {ref, defineProps, defineEmits, onMounted} from 'vue';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useToastStore} from "@/Stores/ToastStore";
const name = 'StudioTagsInput';
const props = defineProps({
    maxlength: {
        type: Number,
        default: 50
    },
    value: {
        type: Array,
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


const emits = defineEmits(['update:modelValue']);

const tags = ref(props.value );

//watch the value prop and update the tags array if it changes
onMounted(() => {
    tags.value = props.value;
});

function addTag(e) {
    if (e.code === 'Enter' || e.code === 'Comma' || e.code === 'Tab') {

        e.preventDefault();
        var val = e.target.value.trim();
        // if the combined length of the tags plus this one is greater than 500 characters, don't add the tag
        if (tags.value.join(',').length + val.length > 500) {
            useToastStore().add({
                'message': 'Combined length of tags must be less than 500 characters',
                'type': 'warning'
            });
            return;
        }
        if (tags.value.indexOf(val) !== -1) {
            useToastStore().add({
                'message': 'Tag already exists',
                'type': 'warning'
            });
            return;
        }
        if (val.length === 0) {
            useToastStore().add({
                'message': 'Tag cannot be empty',
                'type': 'warning'
            });
            return;
        }

        tags.value.push(val);
        emits('update:modelValue', tags.value); // Emit the updated tags array to the parent
        e.target.value = '';
    }
}

function removeTag(index) {
    tags.value.splice(index, 1);
    emits('update:modelValue', tags.value); // Emit the updated tags array to the parent
}

function removeLastTag(e) {
    if (e.target.value.length === 0 && tags.value.length > 0) {
        removeTag(tags.value.length - 1);
    }
}

</script>

<template>
    <consistent-content-holder class="rounded-lg p-2 focus:ring">
        <p class="text-xs font-bold mb-1">Tags</p>

        <div class="flex flex-row flex-wrap gap-2 h-full w-full border-1 border-gray-300 border-box">
            <div
                v-for="(tag, index) in value"
                :key="tag"
                class="flex flex-row gap-x-1 align-middle items-center h-8  bg-zinc-100 dark:bg-zinc-800 rounded-lg px-3 "
            >
                <font-awesome-icon  :icon="['fas', 'close']" class="h-3 cursor-pointer my-auto " @click="removeTag(index)"/>
                <span class="leading-5" v-text="tag"></span>
            </div>
            <input
                type="text"
                placeholder="Add a tag"
                class="bg-transparent h-8 flex-grow max-w-sm
            without-ring border-t-0 border-x-0 border-b-1 border-zinc-300 focus:border-zinc-500 dark:border-zinc-700 focus:dark:border-zinc-500 focus:border-b "
                @keydown="addTag"
                @keydown.delete="removeLastTag"

            />
        </div>
        <InputError v-if="props.error_message" class="mt-2" :message="props.error_message"/>

    </consistent-content-holder>
</template>



