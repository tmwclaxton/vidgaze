
<script setup>
import { faSquareFacebook, faSquareTwitter, faLinkedin, faSquarePinterest, faSquareReddit, faTelegram, faSquareWhatsapp } from "@fortawesome/free-brands-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

import {useShareModalStore} from "@/Stores/ShareModelStore";
import { useToastStore} from "@/Stores/ToastStore";

import {computed} from "vue";
const ShareModalStore = useShareModalStore();
const toastStore = useToastStore();
const platforms = computed(() => [
    {
        name: "Facebook",
        icon: faSquareFacebook,
        link: ShareModalStore.links.facebook,
    },
    {
        name: "Twitter",
        icon: faSquareTwitter,
        link: ShareModalStore.links.twitter,
    },
    {
        name: "LinkedIn",
        icon: faLinkedin,
        link: ShareModalStore.links.linkedin,
    },
    {
        name: "Pinterest",
        icon: faSquarePinterest,
        link: ShareModalStore.links.pinterest,
    },
    {
        name: "Reddit",
        icon: faSquareReddit,
        link: ShareModalStore.links.reddit,
    },
    {
        name: "Telegram",
        icon: faTelegram,
        link: ShareModalStore.links.telegram,
    },
    {
        name: "WhatsApp",
        icon: faSquareWhatsapp,
        link: ShareModalStore.links.whatsapp,
    },
]);

const copyToClipboard = (text) => {
    const el = document.createElement("textarea");
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand("copy");
    document.body.removeChild(el);
    toastStore.add({
        message: "Copied to clipboard",
        type: 'success',
    });
    // ShareModalStore.showMenu = false;
};
</script>
<template>
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-6 m-5 mx-10">
        <p class="flex flex-col items-center" @click="copyToClipboard(ShareModalStore.links.vidgaze)">
            <font-awesome-icon :icon="['fas', 'clipboard']"  class="text-3xl" />
            <span class="text-sm font-medium mt-2">Link</span>
        </p>
        <template v-for="platform in platforms">
            <a  :href="platform.link" class="flex flex-col items-center">
                <FontAwesomeIcon :icon="platform.icon" class="text-3xl" />
                <span class="text-sm font-medium mt-2">{{ platform.name }}</span>
            </a>
        </template>
        <a :href="ShareModalStore.links.email" class="flex flex-col items-center">
            <FontAwesomeIcon icon="envelope" class="text-3xl" />
            <span class="text-sm font-medium mt-2">Email</span>
        </a>
    </div>

    <div class=" max-w-xl w-full mx-auto">
        <div @click="copyToClipboard(ShareModalStore.links.vidgaze)"
             class="overflow-hidden mx-6 bg-zinc-100 dark:bg-vidgaze-blue hover:bg-zinc-200 hover:dark:bg-vidgaze-blue/80 p-2 my-2 mb-4 rounded cursor-text relative flex flex-row">
            <p class="font-semibold pr-8 " v-text="ShareModalStore.links.vidgaze"> </p>
            <div
                class="ml-auto  my-auto   h-full flex flex-row  cursor-pointer gap-x-2">
                <p class=" font-bold text-sm uppercase my-auto">Copy</p>
                <font-awesome-icon :icon="['fas', 'clipboard']"  class="w-4 aspect-square my-auto" />

            </div>
        </div>
    </div>
</template>
