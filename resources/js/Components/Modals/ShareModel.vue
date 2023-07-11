<script setup>
import ExitIcon from '#icons/exit.svg';
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import { vOnClickOutside } from '@vueuse/components';
import {useShareModalStore} from "@/Stores/ShareModelStore";
import SocialMediaLinks from "@/Components/Modals/Partials/SocialMediaLinks.vue";
const ShareModalStore = useShareModalStore();

const name = "ShareModal";

const close = () => {
    if (ShareModalStore.showMenu) {
        ShareModalStore.showMenu = false;
    }
}
const onClickOutsideHandler = [
    (ev) => {
        // console.log(ev)
        close();
    },
]
</script>


<template>
    <div v-if="ShareModalStore.showMenu"  class="z-40 absolute left-1/2 right-1/2 h-max flex flex-row justify-center max-w-xs overflow-hidden">
        <div class="fixed my-auto inset-y-0 h-max">
            <OptionHolder class="shadow-md h-max" v-on-click-outside="onClickOutsideHandler" >
                <!--<div class="w-full flex flex-row p-4 select-none ">-->
                <div class="flex justify-between px-4 py-1  select-none">
                    <p class="text-lg my-auto font-semibold ">Share</p>
                    <div class=" cursor-pointer p-2 rounded-full hover:bg-zinc-100 dark:bg-zinc-900 ml-auto my-auto">
                        <ExitIcon class="w-6 aspect-square " @click="close"/>

                    </div>
                </div>

                <hr class="border-1 border-zinc-300 dark:border-zinc-800 mb-4 mt-2">

                <SocialMediaLinks />

            </OptionHolder>

        </div>

    </div>
</template>

