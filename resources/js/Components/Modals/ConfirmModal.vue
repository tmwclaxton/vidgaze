

<script setup>
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import { useConfirmModalStore } from '@/Stores/ConfirmModelStore';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const confirmModalStore = useConfirmModalStore();

function close() {
    confirmModalStore.show = false;
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-show="confirmModalStore.show"
                class="fixed inset-0 z-[70] flex items-center justify-center p-4 sm:p-6"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="confirm-modal-title"
            >
                <div
                    class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm dark:bg-black/70"
                    aria-hidden="true"
                    @click="close"
                />
                <div
                    class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-2xl shadow-zinc-950/15 dark:border-zinc-700/80 dark:bg-zinc-900 dark:shadow-black/50"
                    @click.stop
                >
                    <button
                        type="button"
                        class="absolute right-3 top-3 rounded-xl p-2 text-zinc-400 transition hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                        data-modal-hide="popup-modal"
                        @click="close"
                    >
                        <FontAwesomeIcon :icon="['fas', 'xmark']" class="h-5 w-5" />
                        <span class="sr-only">Close modal</span>
                    </button>
                    <div class="px-6 pb-2 pt-10 text-center">
                        <span
                            class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400"
                        >
                            <FontAwesomeIcon :icon="['fass', 'circle-exclamation']" class="h-8 w-8" />
                        </span>
                        <h3
                            id="confirm-modal-title"
                            class="text-balance text-base font-semibold text-zinc-800 dark:text-zinc-100 sm:text-lg"
                            v-text="confirmModalStore.title"
                        />
                    </div>
                    <div class="flex flex-col-reverse gap-2 px-6 pb-6 pt-4 sm:flex-row sm:justify-center sm:gap-3">
                        <SecondaryButton class="w-full sm:flex-1" @click="confirmModalStore.clickButtonOne">
                            <span class="mx-auto font-bold" v-text="confirmModalStore.buttonOneText" />
                        </SecondaryButton>
                        <PrimaryButton class="w-full sm:flex-1" @click="confirmModalStore.clickButtonTwo">
                            <span class="mx-auto font-bold" v-text="confirmModalStore.buttonTwoText" />
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
