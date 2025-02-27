<script setup>
import {defineProps} from 'vue';
import {useToastStore} from "@/Stores/ToastStore";
import {useAuthStore} from "@/Stores/AuthStore";
import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";

const props = defineProps({
    platform: {
        type: String,
        required: true
    },
    external_id: {
        type: String,
        required: false,
    },
    text: {
        type: String,
        required: true
    },
    buttonClasses: {
        type: String,
        required: false,
        default: ""
    },

});

function loginRedirect() {
    if (props.external_id) {
        return;
    }
    axios.get(route('api.studio.login', {platform: props.platform}))
        .then(response => {
            window.location.href = response.data.url;
            console.log("redirecting to " + props.platform + " login screen");
        })
        .catch(() => {
            console.error("Something went wrong redirecting to " + props.platform + " login screen");
        });
}

const removePlatform = () => {
    useConfirmModalStore().buttonOneText = 'Go Back';
    useConfirmModalStore().buttonTwoText = 'Remove Platform';
    useConfirmModalStore().title = 'Are you sure, this will unlink your ' + props.platform + ' account and will separate your content from this platform into a placeholder account!';
    useConfirmModalStore().show = true;
    useConfirmModalStore().continue = () => {
        axios.delete(route('api.studio.unlink', {platform: props.platform}))
            .then(() => {
                useAuthStore().getUser();
                useToastStore().add({
                    message: "Successfully unlinked " + props.platform,
                    type: "success"
                });
            })
            .catch(() => {
                console.error("Something went wrong unlinking " + props.platform);
            });
    };
}

</script>
<template>
    <div @click="loginRedirect()" :class="buttonClasses" class="h-full w-max flex flex-row inline-flex items-center gap-2  cursor-pointer
         border border-zinc-200 hover:border-zinc-300 dark:border-zinc-800 dark:hover:border-zinc-700
         dark:bg-vidgaze-blue dark:hover:bg-vidgaze-blue-nav dark:text-zinc-200 text-zinc-900 text-sm font-medium rounded-lg px-5 py-2.5 text-center ">
        <slot></slot>
        <p class="break-words w-full text-left capitalize" v-text="text"/>
        <font-awesome-icon v-if="external_id" @click="removePlatform" :icon="['fas', 'close']" class="w-4 h-4 my-auto text-zinc-600 dark:text-zinc-200"/>
    </div>
</template>

