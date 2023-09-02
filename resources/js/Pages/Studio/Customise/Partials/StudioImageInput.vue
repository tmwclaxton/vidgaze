
<script setup>

import {onMounted, ref, watch} from "vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";

const props = defineProps({
    maxlength: {
        type: Number,
        default: 50
    },
    value: {
        type: String,
        default: ''
    },
    label: {
        type: String,
        default: ''
    },
    description: {
        type: String,
        default: ''
    },
    name: {
        type: String,
        default: ''
    },
    recommendation: {
        type: String,
        default: ''
    },
    rounded: {
        type: Boolean,
        default: true
    },
});

const emits = defineEmits(['update:modelValue','submit']);
const saved = ref(true);
const fileInput = ref(null);
const file = ref(null);
const open = () => {
    fileInput.value.click();

}

const previewFiles = () => {
    console.log("previewFiles");
    saved.value = false;
    file.value = fileInput.value.files[0];
}

const removeFile = () => {
    file.value = null;
    saved.value = true;
}
</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">
        <p class="text-xs font-bold" v-text="props.label"></p>
        <p class="text-xs mx-2 my-1" v-text="props.description"></p>
        <div class="flex flex-col md:flex-row mx-2  my-3">
                <div @click="open"
                     class="w-full  aspect-21/12 md:w-52 flex-shrink-0 bg-zinc-50 dark:bg-zinc-800 rounded h-min cursor-pointer">
                    <div class="flex flex-col w-full h-full py-auto align-items-center">

                            <img class="w-full   my-auto" :src="props.value" :class="[props.rounded ? 'rounded-full aspect-square w-28  m-auto' : ' ']" alt="Profile picture">

                    </div>
                </div>
                <div class="flex flex-col mx-2 w-full">
                    <p class="mt-4 md:mt-0 text-xs  flex-shrink" v-text="props.recommendation"></p>
                    <p class="text-xs flex-shrink text-red-500 mt-2" v-text="saved ? '' : 'Not saved'"></p>
                    <div class="flex flex-row ml-auto gap-x-2 my-2 ">
                        <div class="relative cursor-pointer">
                            <input ref="fileInput" type="file" accept="image/*" @change="previewFiles"
                                   class="cursor-pointer absolute inset-0 z-10 m-0 p-0 w-full h-full outline-none opacity-0"/>
                        </div>
                        <quaternary-button class="float-right " @click="open">
                            Change
                        </quaternary-button>
                        <quaternary-button class="float-right" @click="removeFile">
                            Remove
                        </quaternary-button>
                    </div>
                </div>

            </div>

    </consistent-content-holder>
</template>



