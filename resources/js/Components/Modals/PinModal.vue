<script setup>
import ExitIcon from '#icons/exit.svg';
import OptionHolder from '@/Components/Modals/Partials/OptionHolder.vue';
import Option from '@/Components/Modals/Partials/Option.vue';
import { onMounted } from 'vue';
import { vOnClickOutside } from '@vueuse/components';
import TextInput from '@/Components/Inputs/TextInput.vue';
import { usePinModalStore } from '@/Stores/PinModalStore';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const pinModalStore = usePinModalStore();

onMounted(() => {
    pinModalStore.getVideoCategories();
});

const onClickOutsideHandler = [() => close()];

function close() {
    if (pinModalStore.showMenu) {
        pinModalStore.showMenu = false;
    }
}

function set() {
    pinModalStore.pinVideo();
    pinModalStore.addCategoryToVideo();
}

function removePin() {
    pinModalStore.unpinVideo();
}

function removeCategory() {
    pinModalStore.removeCategoryFromVideo();
}

function removeBoth() {
    pinModalStore.unpinVideo();
    pinModalStore.removeCategoryFromVideo();
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
                v-if="pinModalStore.showMenu"
                class="fixed inset-0 z-[55] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="pin-modal-title"
            >
                <div
                    class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm dark:bg-black/70"
                    aria-hidden="true"
                    @click="close"
                />
                <div class="relative z-10 w-full max-w-3xl" v-on-click-outside="onClickOutsideHandler">
                    <OptionHolder class="max-h-[90vh] min-w-0 overflow-y-auto overflow-x-hidden">
                        <div
                            class="flex items-center justify-between gap-3 border-b border-zinc-200/80 px-4 py-3 dark:border-zinc-800"
                        >
                            <div class="flex min-w-0 items-center gap-2.5">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/12 text-amber-600 dark:bg-amber-500/18 dark:text-amber-400"
                                >
                                    <FontAwesomeIcon :icon="['fas', 'map-pin']" class="h-5 w-5" />
                                </span>
                                <p
                                    id="pin-modal-title"
                                    class="text-base font-semibold tracking-tight text-zinc-900 dark:text-white"
                                >
                                    Pin video
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-xl p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                                @click="close"
                            >
                                <ExitIcon class="h-5 w-5" aria-hidden="true" />
                                <span class="sr-only">Close</span>
                            </button>
                        </div>

                        <div
                            class="flex flex-col border-zinc-200/80 dark:border-zinc-800 sm:flex-row sm:border-t"
                        >
                            <div class="flex min-w-0 flex-1 flex-col border-zinc-200/80 dark:border-zinc-800 sm:border-r">
                                <p
                                    v-if="pinModalStore.selectedCategory != null"
                                    class="border-b border-zinc-200/80 px-3 py-2 text-sm font-medium text-zinc-700 dark:border-zinc-800 dark:text-zinc-200"
                                    v-text="'Category: ' + pinModalStore.selectedCategory.name"
                                />

                                <div class="h-52 overflow-y-auto px-1 py-1 sm:h-60">
                                    <Option
                                        v-if="pinModalStore.categories"
                                        v-for="category in pinModalStore.categories.data"
                                        :key="category.id"
                                        class="w-full items-center"
                                        :class="{
                                            'bg-violet-50 ring-1 ring-violet-200 dark:bg-violet-950/40 dark:ring-violet-500/30':
                                                pinModalStore.selectedCategory?.id === category.id,
                                        }"
                                        @click="pinModalStore.selectedCategory = category"
                                    >
                                        <p
                                            v-text="category.name"
                                            :class="{
                                                'font-semibold text-violet-700 dark:text-violet-300':
                                                    pinModalStore.selectedCategory?.id === category.id,
                                            }"
                                        />
                                    </Option>
                                </div>
                            </div>

                            <div
                                class="flex w-full flex-col gap-3 border-t border-zinc-200/80 p-4 dark:border-zinc-800 sm:w-60 sm:border-t-0 sm:border-l lg:w-64"
                            >
                                <div class="flex flex-col gap-2">
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                        Pin duration (seconds)
                                    </p>
                                    <TextInput
                                        v-model="pinModalStore.duration"
                                        class="h-max w-full"
                                        label="Duration"
                                        type="number"
                                        placeholder="Duration"
                                    />
                                </div>
                                <PrimaryButton class="mx-auto mt-1 h-max w-full max-w-xs" @click="set">
                                    <span class="mx-auto font-bold">Set</span>
                                </PrimaryButton>

                                <DangerButton
                                    v-if="pinModalStore.pinDetails.pinned"
                                    class="mx-auto mt-1 h-max w-full max-w-xs"
                                    @click="removePin"
                                >
                                    <span class="mx-auto font-bold">Remove pin</span>
                                </DangerButton>
                                <DangerButton
                                    v-if="pinModalStore.pinDetails.category_id !== null"
                                    class="mx-auto mt-1 h-max w-full max-w-xs"
                                    @click="removeCategory"
                                >
                                    <span class="mx-auto font-bold">Remove category</span>
                                </DangerButton>
                                <DangerButton
                                    v-if="pinModalStore.pinDetails.category_id !== null && pinModalStore.pinDetails.pinned"
                                    class="mx-auto mt-1 h-max w-full max-w-xs"
                                    @click="removeBoth"
                                >
                                    <span class="mx-auto font-bold">Remove both</span>
                                </DangerButton>
                            </div>
                        </div>
                    </OptionHolder>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
