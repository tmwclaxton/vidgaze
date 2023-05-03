
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
        class: "text-blue-600"
    },
    {
        name: "Twitter",
        icon: faSquareTwitter,
        link: ShareModalStore.links.twitter,
        class: "text-sky-400"
    },
    {
        name: "LinkedIn",
        icon: faLinkedin,
        link: ShareModalStore.links.linkedin,
        class: " text-blue-700"
    },
    {
        name: "Pinterest",
        icon: faSquarePinterest,
        link: ShareModalStore.links.pinterest,
        class: " text-red-600"
    },
    {
        name: "Reddit",
        icon: faSquareReddit,
        link: ShareModalStore.links.reddit,
        class: " text-red-600"
    },
    {
        name: "Telegram",
        icon: faTelegram,
        link: ShareModalStore.links.telegram,
        class: " text-blue-400",
        div_class: "bg-blue-400"
    },
    {
        name: "WhatsApp",
        icon: faSquareWhatsapp,
        link: ShareModalStore.links.whatsapp,
        class: " text-green-400"
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
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-8 gap-6 m-5 mx-10 select-none">
        <p class="flex flex-col items-center group cursor-pointer" @click="copyToClipboard(ShareModalStore.links.vidgaze)">
            <font-awesome-icon :icon="['fas', 'clipboard']"  class="text-3xl group-hover:-translate-y-1 transition ease-in-out duration-200" />
            <span class="text-sm font-medium mt-2">Link</span>
        </p>
        <template v-for="platform in platforms" class="">
            <a  :href="platform.link" class="flex flex-col items-center group">
                <div class="relative group-hover:-translate-y-1 transition ease-in-out duration-200 ">
                    <div class="bg-white absolute z-0  " v-bind:class=" platform.name === 'Telegram' ? 'm-1.5 w-4 h-4' : ' w-5 h-6 m-1' ">

                    </div>
                    <FontAwesomeIcon :icon="platform.icon" class="z-10 relative text-3xl " :class="platform.class" />

                </div>
                 <span class="text-sm font-medium mt-2">{{ platform.name }}</span>
            </a>
        </template>
        <a :href="ShareModalStore.links.email" class="flex flex-col items-center cursor-pointer group">
            <FontAwesomeIcon icon="envelope" class="text-3xl group-hover:-translate-y-1 transition ease-in-out duration-200" />
            <span class="text-sm font-medium mt-2">Email</span>
        </a>
    </div>

    <div class=" max-w-xl w-full mx-auto">
        <div @click="copyToClipboard(ShareModalStore.links.vidgaze)"
             class="overflow-hidden mx-6 bg-zinc-100 dark:bg-vidgaze-blue hover:bg-zinc-200 hover:dark:bg-vidgaze-blue/80 p-2 my-2 mb-4 rounded cursor-text relative flex flex-row">
            <p class="font-semibold pr-8 " v-text="ShareModalStore.links.vidgaze"> </p>
            <div
                class="ml-auto  my-auto   h-full flex flex-row  cursor-pointer gap-x-2">
                <p class=" font-semibold my-auto">Copy</p>
                <font-awesome-icon :icon="['fas', 'clipboard']"  class="w-4 aspect-square my-auto" />

            </div>
        </div>
    </div>
</template>
