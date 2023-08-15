<script setup>
import {defineProps} from 'vue';

const props = defineProps({
    platform: {
        type: String,
        required: true
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
    console.log(props.platform);
    axios.get(route('api.studio.login', {platform: props.platform}))
        .then(response => {
            window.location.href = response.data.url;
            console.log("redirecting to " + props.platform + " login screen");
        })
        .catch(() => {
            console.error("Something went wrong redirecting to " + props.platform + " login screen");
        });
}

</script>
<template>
    <button @click="loginRedirect()" type="button" :class="buttonClasses" class="h-full w-max flex flex-row inline-flex items-center gap-2
         dark:bg-vidgaze-blue dark:hover:bg-vidgaze-blue-nav dark:text-zinc-200 text-zinc-900 text-sm font-medium rounded-lg px-5 py-2.5 text-center ">
        <slot></slot>
        <p class="break-words w-full text-left capitalize" v-text="text"/>
    </button>
</template>

