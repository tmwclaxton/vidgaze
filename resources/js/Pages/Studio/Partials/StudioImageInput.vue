
<script setup>

import {onMounted, ref, watch} from "vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useAuthStore} from "@/Stores/AuthStore";
import {useToastStore} from "@/Stores/ToastStore";
import InputError from "@/Components/Inputs/InputError.vue";

const props = defineProps({
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
    recommendation: {
        type: String,
        default: ''
    },
    rounded: {
        type: Boolean,
        default: true
    },
    endpoint: {
        type: String,
        default: ''
    },
    error_message: {
        type: String,
        default: null
    },
    triggerSave: {
        type: Boolean,
        default: false
    }
});

watch(() => props.triggerSave, (value) => {
    if (value) {
        save();
    }
});

const name = 'StudioImageInput';

const emits = defineEmits(['submit','refresh','update:thumbnail-different']);
const error_message_local = ref(props.error_message);
watch(() => props.error_message, (value) => {
    error_message_local.value = value;
});
const saved = ref(true);
const fileInput = ref(null);
const image = ref(null);
const imageUrl = ref(null);
const open = () => {
    fileInput.value.click();
}

const previewFiles = () => {
    if (!fileInput.value.files[0]) {
        return;
    }

    emits('update:thumbnail-different', true);
    saved.value = false;
    image.value = fileInput.value.files[0];
    const reader = new FileReader();
    reader.readAsDataURL(image.value);
    reader.onload = e => {
        imageUrl.value = e.target.result;
    };

}

const removeFile = () => {
    if (imageUrl.value) {
        // console.log('image url is set so lets go back to default image')
        emits('update:thumbnail-different', false);
        image.value = null;
        imageUrl.value = null;
        saved.value = true;
        return;
    }
    if (!image.value) {
        // console.log('image is not set so that means user wants to remove their current image')
        emits('update:thumbnail-different', true);
        imageUrl.value = 'https://htmlcolorcodes.com/assets/images/colors/white-color-solid-background-1920x1080.png';
        saved.value = false;
        return;
    }
    // console.log('image is set but image url is not set, user was going to upload a new image but changed their mind')
    emits('update:thumbnail-different', false);
    image.value = null;
    saved.value = true;
    imageUrl.value = null;
}

const save = () => {
    saved.value = true;
    if (!image.value) {
        axios.patch(props.endpoint, {
            image: null
        }).then(response => {
            emits('refresh');
            emits('update:thumbnail-different', false);
            error_message_local.value = null;
            useToastStore().add({
                message: response.data.message,
                type: response.data.toastType
            });
        }).catch(error => {
            error_message_local.value = error.response.data.message;
            useToastStore().add({
                message: error.response.data.message,
                type: "warning"
            });
        });
        imageUrl.value = null;
        return;
    }
    const formData = new FormData();
    formData.append('image', image.value);
    axios.patch(props.endpoint, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    }).then(response => {
        emits('refresh');
        emits('update:thumbnail-different', false);
        error_message_local.value = null;
        useToastStore().add({
            message: response.data.message,
            type: response.data.toastType
        });
    }).catch(error => {
        error_message_local.value = error.response.data.message;
        useToastStore().add({
            message: error.response.data.message,
            type: "warning"
        });
    });
    imageUrl.value = null
}

</script>

<template>
    <consistent-content-holder class="rounded p-2 focus:ring">
        <p class="text-xs font-bold" v-text="props.label"></p>
        <p class="text-xs mx-2 my-1" v-text="props.description"></p>
        <div class="flex flex-col md:flex-row mx-2  my-3">
            <div @click="open" :class="[props.rounded ? 'py-2' : '']"
                 class="w-full group  overflow-hidden aspect-21/12 md:w-52 flex-shrink-0 bg-zinc-50 dark:bg-zinc-800 rounded h-min cursor-pointer">
                <div class=" relative flex flex-col h-full justify-center items-center" :class="[props.rounded ? 'rounded-full overflow-hidden aspect-square mx-auto w-max' : 'w-full']">

                    <img class="w-full group-hover:blur-sm transition-all duration-300 ease-in-out group-hover:brightness-50"
                         :class="[props.rounded ? 'h-full ' : 'my-auto']"
                         v-if="props.value || imageUrl"
                         :src="imageUrl ? imageUrl : props.value"/>

                    <!-- Centering the Font Awesome icon vertically -->
                    <font-awesome-icon :icon="['fas', 'circle-plus']" class="text-zinc-300 h-8 absolute mx-auto inset-0 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-70 pointer-events-none transition-all duration-300 ease-in-out"></font-awesome-icon>

                </div>
            </div>

            <div class="flex flex-col md:flex-row px-2 justify-between w-full">
                <div class="flex flex-col mx-2 w-max  ">
                    <p class="mt-4 md:mt-0 text-xs  flex-shrink" v-text="props.recommendation"></p>
                    <p class="text-xs flex-shrink text-red-500 mt-2" v-text="saved ? '' : 'Not saved'"></p>


                </div>
                <div class="flex flex-col lg:flex-row flex-wrap space-x-2 space-y-2 mb-2 h-max ">
                    <div class="relative cursor-pointer">
                        <input ref="fileInput" type="file" accept="image/*" @change="previewFiles"

                               class="cursor-pointer absolute inset-0 z-10 m-0 p-0 w-full h-full outline-none opacity-0"/>
                    </div>
                    <quaternary-button v-if="!saved"
                                       class="float-right h-max" @click="save">
                        <font-awesome-icon icon="save" class="mr-2"/>
                        Save
                    </quaternary-button>
                    <quaternary-button class="float-right h-max " @click="open">
                        <font-awesome-icon icon="edit" class="mr-2"/>
                        Change
                    </quaternary-button>
                    <quaternary-button class="float-right h-max" @click="removeFile" v-if="props.value">
                        <font-awesome-icon icon="trash" class="mr-2"/>
                        Remove
                    </quaternary-button>
                </div>
            </div>

        </div>
        <InputError v-if="error_message_local !== null" class="ml-3 mt-2" :message="error_message_local"/>

    </consistent-content-holder>
</template>



