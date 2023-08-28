<template>
    <div @click="featureCreator">
        <QuaternaryButton >
            <font-awesome-icon :icon="['fas', 'medal']" class="h-5"/>
            <span class="font-semibold">Feature Creator</span>
        </QuaternaryButton>
    </div>
</template>

<script setup>

import QuaternaryButton from "@/Components/Buttons/QuaternaryButton.vue";
import {useToastStore} from "@/Stores/ToastStore";
const toastStore = useToastStore();
const props = defineProps({
    creator_id: {
        type: Number,
        required: true
    }
});

const name = 'FeatureCreatorButton';

const featureCreator = () => {
    // console.log(props.creator_id)
    axios.post(route('api.creator.feature.toggle'), {
        creator_id: props.creator_id
    })
        .then(response => {
            if (response.data.message.length > 0) {
                toastStore.add({
                    message: response.data.message,
                    type: response.data.toastType
                });
            }
        })
        .catch(error => {
            toastStore.add({
                message: 'Something went wrong, please try again later',
                type: 'warning'
            });
        });

}

</script>
