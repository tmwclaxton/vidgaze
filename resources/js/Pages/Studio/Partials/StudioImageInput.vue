
<script setup>

import {onMounted, ref, watch} from "vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useAuthStore} from "@/Stores/AuthStore";
import {useToastStore} from "@/Stores/ToastStore";

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
});

const emits = defineEmits(['submit']);
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
        // set back to default image
        image.value = null;
        imageUrl.value = null;
        saved.value = true;
        return;
    }
    if (!image.value) {
        // this means user wants to remove their current image and revert to the default one
        // emits('update:modelValue', null)
        imageUrl.value = "https://img.freepik.com/free-photo/abstract-luxury-plain-blur-grey-black-gradient-used-as-background-studio-wall-display-your-products_1258-63747.jpg?w=2000";
        saved.value = false;

        return;
    }
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
            useAuthStore().getUser();
            useToastStore().add({
                message: response.data.message,
                type: response.data.toastType
            });
        }).catch(error => {
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
        useAuthStore().getUser();
        useToastStore().add({
            message: response.data.message,
            type: response.data.toastType
        });
    }).catch(error => {
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
                <div @click="open"
                     class="w-full  overflow-hidden aspect-21/12 md:w-52 flex-shrink-0 bg-zinc-50 dark:bg-zinc-800 rounded h-min cursor-pointer py-2">
                    <div class="flex flex-col h-full align-middle justify-middle "  :class="[props.rounded ? 'rounded-full overflow-hidden aspect-square  mx-auto w-max' : 'w-full']">

                            <img class="w-full  " :class="[props.rounded ? 'h-full ' : 'my-auto']"
                                 v-if="imageUrl || props.value"
                                 :src="imageUrl ? imageUrl : props.value">

                    </div>
                </div>
                <div class="flex flex-col lg:flex-row ml-auto space-y-2 gap-x-2 mb-2 h-max ">
                    <div class="flex flex-col mx-2 w-full ">
                        <p class="mt-4 md:mt-0 text-xs  flex-shrink" v-text="props.recommendation"></p>
                        <p class="text-xs flex-shrink text-red-500 mt-2" v-text="saved ? '' : 'Not saved'"></p>


                    </div>
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
                    <quaternary-button class="float-right h-max" @click="removeFile">
                        <font-awesome-icon icon="trash" class="mr-2"/>
                        Remove
                    </quaternary-button>
                </div>

            </div>

    </consistent-content-holder>
</template>



