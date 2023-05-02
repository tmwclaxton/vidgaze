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
    <div v-if="ShareModalStore.showMenu"  class="z-40 absolute left-1/2 right-1/2 flex-grow h-max flex flex-row justify-center">
        <div class="fixed my-auto inset-y-0 h-max">
            <OptionHolder class="min-w-64 shadow-md h-max" v-on-click-outside="onClickOutsideHandler" >
                <!--<div class="w-full flex flex-row p-4 select-none ">-->
                <div class="flex justify-between px-4 py-2  ">
                    <p class="text-lg my-auto font-semibold ">Share</p>
                    <ExitIcon class="w-6 aspect-square ml-auto my-auto cursor-pointer" @click="close"/>
                </div>

                <hr class="border-1 border-zinc-300 dark:border-zinc-800 mb-4 mt-2">
                <!--<div   v-for="link in ShareModalStore.links" >-->
                <!--    <a :href="link">-->
                <!--        <p>test</p>-->
                <!--    </a>-->
                <!--</div>-->
                <SocialMediaLinks />

            </OptionHolder>

        </div>

    </div>
</template>

