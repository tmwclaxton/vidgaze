

<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import DateInput from "@/Components/Inputs/DateInput.vue";

const name = 'StudioMadeForKidsInput';
const props = defineProps({
    value: {
        type: String,
        default: ''
    },
    publish_time: {
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
    errors: {
        type: Array,
        default: null
    }
});

const emits = defineEmits(['update:modelValue','submit']);

const submit = () => {
    // deselect
    // textarea.value.blur();
    emits('update:modelValue');
}
const visibility = ref(props.value);
const publishing_time = ref(props.publish_time);
</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">
        <div>
            <InputLabel class="mb-1" for="visibility" value="Visibility (Required)"/>
            <div class="space-y-2">
                <div class="flex items-center">
                    <input type="radio" id="private" value="private" v-model="visibility" class="mr-2">
                    <label for="private">Private</label>
                </div>
                <div class="flex items-center">
                    <input type="radio" id="unlisted" value="unlisted" v-model="visibility" class="mr-2">
                    <label for="unlisted">Unlisted</label>
                </div>
                <div class="flex items-center">
                    <input type="radio" id="public" value="public" v-model="visibility" class="mr-2">
                    <label for="public">Public</label>
                </div>
                <div>
                    <div class="flex items-center"><input type="radio" id="scheduled" value="scheduled" v-model="visibility" class="mr-2">
                        <label for="scheduled">Schedule</label></div>
                    <DateInput class="mt-2" v-if="visibility === 'scheduled'" v-model="publishing_time"/>
                    <InputError class="mt-2" :message="errors.publish_time ? errors.publish_time[0] : null"/>
                </div>
            </div>
            <InputError class="mt-2" :message="errors.visibility ? errors.visibility[0] : null"/>
        </div>

    </consistent-content-holder>
</template>



