<template>
    <div :class="getClasses" class=" w-max rounded-xl flex flex-row items-center  overflow-hidden px-3 h-6 w-max ">
        <slot class="w-3 h-3 mr-1.5"></slot>

        <YouTubeIcon v-if="source === 'YouTube'" class="w-4 mr-1.5" />
        <TwitchIcon v-if="source === 'Twitch'" class="w-3 h-3 mr-1.5" />
        <!--<FacebookIcon v-if="source === 'Facebook'" class="w-3 h-3 mr-1.5"/>-->
        <!--<InstagramIcon v-if="source === 'Instagram'" class="w-3 h-3 mr-1.5"/>-->
        <VimeoIcon v-if="source === 'Vimeo'" class="w-3 h-3 mr-1.5" />
        <DailymotionIcon v-if="source === 'Dailymotion'" class="w-3 h-3 mr-1.5" />
        <RumbleIcon src="/" v-if="source === 'Rumble'" class="w-3 h-3 mr-1.5" />
        <BitChuteIcon v-if="source === 'BitChute'" class="w-3 h-3 mr-1.5" />
        <OdyseeIcon v-if="source === 'Odysee'" class="w-3 h-3 mr-1.5" />
        <TikTokIcon v-if="source === 'Tiktok' || source === 'TikTok'" class="w-3 h-3 mr-1.5" />

        <p class="line-clamp-1 leading-tight font-bold text-xs" v-text="text"></p>
    </div>
</template>

<script setup>
import YouTubeIcon from '~/images/icons/youtube.svg';
import TwitchIcon from '~/images/icons/twitch.svg';
import FacebookIcon from '~/images/icons/facebook.svg';
import InstagramIcon from '~/images/icons/instagram.svg';
import VimeoIcon from '~/images/icons/vimeo.svg';
import DailymotionIcon from '~/images/icons/dailymotion.svg';
import RumbleIcon from '~/images/icons/rumble.svg';
import BitChuteIcon from '~/images/icons/bitchute.svg';
import OdyseeIcon from '~/images/icons/odysee.svg';
import TikTokIcon from '~/images/icons/tiktok.svg';

import { defineProps, computed } from 'vue';

const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    source: {
        type: String,
        required: false,
    },
    extraClasses: {
        type: String,
        required: false,
    },
});

// Defined background color classes based on source light/dark mode and source
const sourceClasses = {
    YouTube: 'bg-red-400 dark:bg-red-900',
    Twitch: 'bg-purple-400 dark:bg-purple-900',
    Dailymotion: 'bg-black dark:bg-black',
    Vimeo: 'bg-blue-500 dark:bg-blue-700',
    Rumble: 'bg-green-900 dark:bg-green-900',
    TikTok: 'bg-black dark:bg-blue-800',
    Tiktok: 'bg-black dark:bg-blue-800',
    BitChute: 'bg-orange-700 dark:bg-orange-900',
    Odysee: 'bg-sky-700 dark:bg-sky-900',
};

const getClasses = computed(() => {
    const classes = sourceClasses[props.source];

    if (!classes) {
        return props.extraClasses ?? ' bg-vidgaze-blue text-zinc-100 dark:text-white  text-xs';
    }

    return classes + ' text-white';
});
</script>
