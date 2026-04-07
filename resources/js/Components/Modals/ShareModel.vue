<script setup>
import ExitIcon from '#icons/exit.svg';
import SocialMediaLinks from '@/Components/Modals/Partials/SocialMediaLinks.vue';
import { vOnClickOutside } from '@vueuse/components';
import { useShareModalStore } from '@/Stores/ShareModelStore';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const shareModalStore = useShareModalStore();

function close() {
    shareModalStore.showMenu = false;
}

const onClickOutsideHandler = [() => close()];
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
                v-if="shareModalStore.showMenu"
                class="fixed inset-0 z-[55] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="share-modal-title"
            >
                <div
                    class="absolute inset-0 bg-zinc-950/60 backdrop-blur-sm dark:bg-black/70"
                    aria-hidden="true"
                    @click="close"
                />
                <div
                    class="relative z-10 w-full max-w-lg"
                    v-on-click-outside="onClickOutsideHandler"
                >
                    <div
                        class="max-h-[90vh] overflow-y-auto overflow-x-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-2xl shadow-zinc-950/15 dark:border-zinc-700/80 dark:bg-zinc-900 dark:shadow-black/50"
                    >
                        <header
                            class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-zinc-200/80 bg-white/95 px-5 py-4 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-900/95"
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-500/12 text-violet-600 dark:bg-violet-500/18 dark:text-violet-400"
                                >
                                    <FontAwesomeIcon :icon="['fas', 'share-nodes']" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <h2
                                        id="share-modal-title"
                                        class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white"
                                    >
                                        Share
                                    </h2>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Copy the link or open a platform
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-xl p-2 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                                @click="close"
                            >
                                <ExitIcon class="h-5 w-5" aria-hidden="true" />
                                <span class="sr-only">Close share dialog</span>
                            </button>
                        </header>
                        <SocialMediaLinks />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
