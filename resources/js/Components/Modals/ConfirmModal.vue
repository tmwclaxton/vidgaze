

<script setup>
 import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
 import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
 import {useConfirmModalStore} from "@/Stores/ConfirmModelStore";
 import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
 const confirmModalStore = useConfirmModalStore();
 const name = 'ConfirmModal';

 // watch

</script>

<template>

    <div v-show="confirmModalStore.show" class="z-50 absolute left-1/2 right-1/2 flex-grow h-max flex flex-row justify-center">
        <ConsistentContentHolder class="fixed my-auto inset-y-0 h-max bg-zinc-100 dark:bg-vidgaze-blue-dropdown rounded-xl select-none w-96">
            <!--Close modal-->
            <button @click="confirmModalStore.show = false" type="button" class="absolute top-3 right-2.5  p-1.5 ml-auto inline-flex items-center
            text-sm text-zinc-400 hover:text-zinc-900 dark:hover:text-white
            bg-transparent hover:bg-zinc-200 dark:hover:bg-zinc-800 rounded-lg " data-modal-hide="popup-modal">
                <font-awesome-icon :icon="['fas', 'xmark']" class="w-5 h-5"  />
                <span class="sr-only">Close modal</span>
            </button>
            <div class="p-6 text-center">
                <font-awesome-icon :icon="['fass', 'circle-exclamation']"  class="mx-auto mb-4 text-zinc-400 w-14 h-14 dark:text-zinc-200" />
                <h3 class="  text-lg font-normal text-zinc-500 dark:text-zinc-400" v-text="confirmModalStore.title"/>
            </div>
            <div class="flex flex-row justify-center gap-2 px-5 pb-5">
                <SecondaryButton class="w-full" @click="confirmModalStore.clickButtonOne">
                    <p class="mx-auto font-bold" v-text="confirmModalStore.buttonOneText"/>
                </SecondaryButton>
                <PrimaryButton class="w-full" @click="confirmModalStore.clickButtonTwo">
                    <p class="mx-auto font-bold" v-text="confirmModalStore.buttonTwoText"/>
                </PrimaryButton>
            </div>
        </ConsistentContentHolder>
    </div>
</template>
