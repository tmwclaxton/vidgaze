<script setup>
import {
    faSquareFacebook,
    faSquareTwitter,
    faLinkedin,
    faSquarePinterest,
    faSquareReddit,
    faTelegram,
    faSquareWhatsapp,
} from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { useShareModalStore } from '@/Stores/ShareModelStore';
import { useToastStore } from '@/Stores/ToastStore';
import { computed } from 'vue';

const ShareModalStore = useShareModalStore();
const toastStore = useToastStore();

const platforms = computed(() => [
    {
        name: 'Facebook',
        icon: faSquareFacebook,
        link: ShareModalStore.links.facebook,
        class: 'text-[#1877F2] dark:text-[#4d9fff]',
    },
    {
        name: 'Twitter',
        icon: faSquareTwitter,
        link: ShareModalStore.links.twitter,
        class: 'text-sky-500 dark:text-sky-400',
    },
    {
        name: 'LinkedIn',
        icon: faLinkedin,
        link: ShareModalStore.links.linkedin,
        class: 'text-[#0A66C2] dark:text-[#5aadff]',
    },
    {
        name: 'Pinterest',
        icon: faSquarePinterest,
        link: ShareModalStore.links.pinterest,
        class: 'text-rose-600 dark:text-rose-400',
    },
    {
        name: 'Reddit',
        icon: faSquareReddit,
        link: ShareModalStore.links.reddit,
        class: 'text-orange-600 dark:text-orange-400',
    },
    {
        name: 'Telegram',
        icon: faTelegram,
        link: ShareModalStore.links.telegram,
        class: 'text-sky-500 dark:text-sky-400',
    },
    {
        name: 'WhatsApp',
        icon: faSquareWhatsapp,
        link: ShareModalStore.links.whatsapp,
        class: 'text-emerald-600 dark:text-emerald-400',
    },
]);

function copyToClipboard(text) {
    const el = document.createElement('textarea');
    el.value = text;
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    toastStore.add({
        message: 'Copied to clipboard',
        type: 'success',
    });
}
</script>

<template>
    <div class="px-4 pb-5 pt-4 sm:px-5">
        <p class="mb-3 text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
            Quick share
        </p>
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 sm:gap-3">
            <button
                type="button"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-transparent bg-zinc-100/90 px-2 py-4 transition hover:border-violet-300/50 hover:bg-violet-50/80 dark:bg-zinc-800/80 dark:hover:border-violet-500/30 dark:hover:bg-violet-950/40"
                @click="copyToClipboard(ShareModalStore.links.vidgaze)"
            >
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-violet-600 shadow-sm dark:bg-zinc-900 dark:text-violet-400"
                >
                    <FontAwesomeIcon :icon="['fas', 'link']" class="text-xl transition group-hover:-translate-y-0.5" />
                </span>
                <span class="text-center text-xs font-semibold text-zinc-700 dark:text-zinc-200">Copy link</span>
            </button>

            <template v-for="platform in platforms" :key="platform.name">
                <a
                    :href="platform.link"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group flex flex-col items-center gap-2 rounded-2xl border border-transparent bg-zinc-100/90 px-2 py-4 transition hover:border-zinc-300/80 hover:bg-white dark:bg-zinc-800/80 dark:hover:border-zinc-600 dark:hover:bg-zinc-800"
                >
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow-sm dark:bg-zinc-900"
                    >
                        <FontAwesomeIcon
                            :icon="platform.icon"
                            class="text-2xl transition group-hover:-translate-y-0.5"
                            :class="platform.class"
                        />
                    </span>
                    <span class="text-center text-xs font-semibold text-zinc-700 dark:text-zinc-200">{{
                        platform.name
                    }}</span>
                </a>
            </template>

            <a
                :href="ShareModalStore.links.email"
                class="group flex flex-col items-center gap-2 rounded-2xl border border-transparent bg-zinc-100/90 px-2 py-4 transition hover:border-zinc-300/80 hover:bg-white dark:bg-zinc-800/80 dark:hover:border-zinc-600 dark:hover:bg-zinc-800"
            >
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-zinc-600 shadow-sm dark:bg-zinc-900 dark:text-zinc-300"
                >
                    <FontAwesomeIcon
                        :icon="['fas', 'envelope']"
                        class="text-2xl transition group-hover:-translate-y-0.5"
                    />
                </span>
                <span class="text-center text-xs font-semibold text-zinc-700 dark:text-zinc-200">Email</span>
            </a>
        </div>

        <p class="mb-2 mt-6 text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
            Page link
        </p>
        <button
            type="button"
            class="flex w-full items-center gap-3 rounded-xl border border-zinc-200/90 bg-zinc-50/90 px-3 py-2.5 text-left transition hover:border-violet-300/60 hover:bg-violet-50/50 dark:border-zinc-700 dark:bg-zinc-800/50 dark:hover:border-violet-500/40 dark:hover:bg-violet-950/20"
            @click="copyToClipboard(ShareModalStore.links.vidgaze)"
        >
            <FontAwesomeIcon :icon="['fas', 'clipboard']" class="shrink-0 text-zinc-400 dark:text-zinc-500" />
            <p
                class="min-w-0 flex-1 break-all font-mono text-xs text-zinc-800 dark:text-zinc-200 sm:text-sm"
                v-text="ShareModalStore.links.vidgaze"
            />
            <span
                class="shrink-0 rounded-lg bg-violet-100 px-2 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-500/20 dark:text-violet-300"
            >
                Copy
            </span>
        </button>
    </div>
</template>
