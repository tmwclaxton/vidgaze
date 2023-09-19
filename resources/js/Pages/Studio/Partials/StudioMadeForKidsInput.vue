
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import {computed, onMounted, ref, watch} from "vue";
import InputError from "@/Components/Inputs/InputError.vue";

const name = 'StudioMadeForKidsInput';
const props = defineProps({
    value: {
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

const submit = (value) => {
    emits('update:modelValue', value);
}

const audience = ref(props.value);
const expand = ref(props.value === 'mature');
watch(() => props.value, () => {
    audience.value = props.value;
});
</script>

<template>
    <consistent-content-holder >
        <div class="rounded p-2 focus:ring">
            <p class="text-xs font-bold">Is this video / stream made for kids? (required) </p>
            <p class="text-xs mx-2 my-1">Regardless of your location, you're legally
                required to comply with the Children's Online Privacy Protection Act (COPPA)
                and/or other laws. You're required to tell us whether your videos / streams are made
                for kids. </p>
            <div class="p-2">
                <div class="flex items-center mb-4">
                    <input
                        id="is_for_kids"
                        type="radio"
                        name="audience"
                        class="select-none cursor-pointer w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                        value="kids"
                        v-model="audience"
                        :checked="audience === 'kids'"
                        @click="submit('kids')"
                    >
                    <label for="is_for_kids"
                           class="cursor-pointer ml-2 text-sm font-medium text-zinc-900 dark:text-zinc-200">
                        Yes, it's made for kids
                    </label>
                </div>
                <div class="flex items-center ">
                    <input
                        id="not_for_kids"
                        type="radio"
                        name="audience"
                        class="select-none cursor-pointer w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                        value="all"
                        v-model="audience"
                        :checked="audience === 'all'"
                        @click="submit('all')"

                    >

                    <label for="not_for_kids"
                           class="cursor-pointer ml-2 text-sm font-medium text-zinc-900 dark:text-zinc-200">
                        No, it's not made for kids </label>
                </div>
                <template v-if="audience !== 'kids'">
                    <div>
                        <div @click="expand = !expand;" class="select-none flex flex-row cursor-pointer mt-3 mb-1">
                            <!--<x-icon class="fill w-3 mr-2" name="extend-down"/>-->
                            <font-awesome-icon :icon="['fas', 'chevron-down']" class="fill w-3 mr-2"/>
                            <p class="text-xs font-bold ">Age Restriction (advanced)</p>
                        </div>
                        <div v-show="expand" >
                            <p class="text-xs mb-2">Age-restricted videos may have limited to no monetisation by platforms like YouTube or Dailymotion.</p>
                            <div class="flex items-center ">
                                <input
                                    id="mature"
                                    type="radio"
                                    name="audience"
                                    class="select-none cursor-pointer w-4 h-4 text-blue-600 bg-zinc-100 border-zinc-300 without-ring dark:bg-zinc-700 dark:border-zinc-600"
                                    value="mature"
                                    v-model="audience"
                                    :checked="audience === 'mature'"
                                    @click="submit('mature')"
                                >

                                <label for="mature"
                                       class="cursor-pointer ml-2 text-sm font-medium text-zinc-900 dark:text-zinc-200">
                                    Yes, restrict my video to viewers over 18 </label>
                            </div>
                        </div>

                    </div>
                </template>
            </div>
        </div>

        <InputError v-if="props.error_message" class="mt-2" :message="props.error_message"/>

    </consistent-content-holder>
</template>



