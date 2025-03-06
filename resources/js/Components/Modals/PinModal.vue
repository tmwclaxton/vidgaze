<script setup>
import TickIcon from '#icons/tick.svg';
import ClockIcon from '#icons/clock.svg';
import ShareIcon from '#icons/share.svg';
import PlaylistIcon from '#icons/add2playlist.svg';
import ExitIcon from '#icons/exit.svg';
import Checkbox from "@/Components/Inputs/Checkbox.vue";
import OptionHolder from "@/Components/Modals/Partials/OptionHolder.vue";
import Option from "@/Components/Modals/Partials/Option.vue";
import { ref, onMounted } from 'vue';
import { vOnClickOutside } from '@vueuse/components';
import TextInput from "@/Components/Inputs/TextInput.vue";
import SelectInput from "@/Components/Inputs/SelectInput.vue";
import {usePinModalStore} from "@/Stores/PinModalStore";
const pinModalStore = usePinModalStore();
import {useToastStore} from "@/Stores/ToastStore";
import CreatePlaylistPartial from "@/Components/Modals/Partials/CreatePlaylistPartial.vue";
import {usePlaylistModalStore} from "@/Stores/PlaylistModalStore";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
const toastStore =  useToastStore();

onMounted(() => {
    pinModalStore.getVideoCategories();
});

const name = "PinModal";



const ignoreElRef = ref();
const onClickOutsideHandler = [
    (ev) => {
        // console.log(ev)
        close();
    },
    { ignore: [ignoreElRef] }
]

const close = () => {
    if (pinModalStore.showMenu) {
        pinModalStore.showMenu = false;
    }
}

const set = () => {
    pinModalStore.pinVideo();
    pinModalStore.addCategoryToVideo();
}

const removePin = () => {
    pinModalStore.unpinVideo();
}

const removeCategory = () => {
    pinModalStore.removeCategoryFromVideo();
}

const removeBoth = () => {
    pinModalStore.unpinVideo();
    pinModalStore.removeCategoryFromVideo();
}


</script>


<template>
    <div v-if="pinModalStore.showMenu"  class="pointer-events-none z-40 absolute left-1/2 right-1/2 flex-grow h-max w-max flex flex-row justify-center">
        <div class="pointer-events-none  fixed my-auto inset-y-0 h-max flex">
            <OptionHolder class="min-w-64  shadow-md h-max mx-auto pointer-events-auto" v-on-click-outside="onClickOutsideHandler" >
                <!--<div class="w-full flex flex-row p-4  ">-->
                <div class="flex justify-between px-4 py-2  w-full">
                    <p class="text-lg my-auto font-semibold ">Pin Video</p>
                    <ExitIcon class="w-6 aspect-square ml-auto my-auto cursor-pointer" @click="close"/>
                </div>

                <hr class="">

                <div class="flex flex-row w-96 border-y-1 border-zinc-300 dark:border-zinc-800 my-1 ">
                    <div class="flex flex-col">
                        <p class="border-b-2 border-zinc-300 dark:border-zinc-800" v-if="pinModalStore.selectedCategory !== null && pinModalStore.selectedCategory !== undefined"
                           v-text="'Category: ' + pinModalStore.selectedCategory.name"/>

                        <div class="h-52 overflow-y-auto  mr-1 pr-2  ">
                            <Option v-if="pinModalStore.categories" class="items-center w-full"
                                    v-bind:class="{'bg-zinc-100 dark:bg-zinc-800': pinModalStore.selectedCategory !== null && pinModalStore.selectedCategory !== undefined && pinModalStore.selectedCategory.id === category.id}"
                                    v-for="category in pinModalStore.categories.data" :key="category.id"
                                    @click="pinModalStore.selectedCategory = category">
                                <!--                        <Checkbox :checked="playlist.videos_present_in_playlist" class="my-auto" :id="'playlist_' + playlist.id" :name="'playlist_' + playlist.id" :value="playlist.id" />-->


                                <p v-text="category.name"
                                   v-bind:class="{'underline ': pinModalStore.selectedCategory !== null && pinModalStore.selectedCategory !== undefined && pinModalStore.selectedCategory.id === category.id}"></p>

                                <!--                        <span class="flex-grow"/>-->
                                <!--                        <font-awesome-icon :icon="['fas', 'lock']" v-if="playlist.visibility === 'private'"/>-->
                                <!--                        <font-awesome-icon :icon="['fas', 'earth-americas']" v-if="playlist.visibility === 'public'"/>-->
                                <!--                        <font-awesome-icon :icon="['fas', 'link']" v-if="playlist.visibility === 'unlisted'"/>-->
                            </Option>
                        </div>
                    </div>
                    <div class="flex flex-col w-52 border-zinc-300 dark:border-zinc-800 pl-2 border-l-2">

                        <div class="flex flex-row gap-x-2">
                            <p>Pin Duration (seconds)</p>
                            <TextInput class="w-full h-max" v-model="pinModalStore.duration" label="Duration"
                                       type="number"
                                       placeholder="Duration"/>
                        </div>
                        <PrimaryButton class="w-max mx-auto mt-2  h-max" @click="set">
                            <p class="mx-auto">Set</p>
                        </PrimaryButton>

                        <DangerButton class="w-max mx-auto mt-2  h-max" @click="removePin" v-if="pinModalStore.pinDetails.pinned">
                            <p class="mx-auto">Remove Pin</p>
                        </DangerButton>
                        <DangerButton class="w-max mx-auto mt-2  h-max" @click="removeCategory" v-if="pinModalStore.pinDetails.category_id !== null">
                            <p class="mx-auto">Remove Category</p>
                        </DangerButton>
                        <DangerButton class="w-max mx-auto mt-2  h-max" @click="removeBoth" v-if="pinModalStore.pinDetails.category_id !== null && pinModalStore.pinDetails.pinned">
                            <p class="mx-auto">Remove Both</p>
                        </DangerButton>



                    </div>
                </div>



            </OptionHolder>

        </div>

    </div>
</template>

