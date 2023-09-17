
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";
import InputLabel from "@/Components/Inputs/InputLabel.vue";
import Dropdown from "@/Components/Inputs/Dropdown.vue";


const name = 'StudioMadeForKidsInput';
const props = defineProps({
    value: {
        type: Number,
        default: null
    },
    categories: {
        type: Array,
        default: ''
    },
    errors: {
        type: Object,
        default: null
    }
});

const emits = defineEmits(['update:modelValue']);

const category_id_local = ref(props.value);



</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">

            <p class="text-xs font-bold">Category</p>
            <p class="text-xs mx-1 my-1">Choose a category that best describes your video</p>

            <Dropdown
                @update:modelValue="emits('update:modelValue', $event)"
                v-model="category_id_local"
                name="category_id"
                id="category_id"
                :items="props.categories"
                required
            />
            <InputError class="mt-2" :message="errors.category_id ? errors.category_id[0] : null"/>
    </consistent-content-holder>
</template>



