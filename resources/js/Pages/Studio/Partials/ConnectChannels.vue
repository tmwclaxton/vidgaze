<template>
    <ConsistentContentHolder class="p-5 h-full">
        <p class="font-bold text-lg">Connect your Channels to VidGaze</p>

        <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-5"></div>

        <div class="flex flex-row flex-wrap gap-2 ">
            <StudioLinkButton link="/studio/youtube" text="Sign in with YouTube" buttonClasses="bg-red-200 hover:bg-red-300">
                <YouTubeIcon class="w-6 h-6 my-auto text-zinc-200"/>
            </StudioLinkButton>
            <StudioLinkButton link="/studio/youtube" text="Sign in with Dailymotion" buttonClasses="bg-zinc-200 hover:bg-zinc-300">
                <DailyMotionIcon class="w-6 h-6 my-auto  "/>
            </StudioLinkButton>
            <StudioLinkButton link="/studio/youtube" text="Sign in with Twitch" buttonClasses="bg-purple-200 hover:bg-purple-300">
                <TwitchIcon class="w-6 h-6 my-auto "/>
            </StudioLinkButton>
            <StudioLinkButton link="/studio/vimeo" text="Sign in with Vimeo" buttonClasses="bg-blue-200 hover:bg-blue-300">
                <VimeoIcon class="w-6 h-6 my-auto "/>
            </StudioLinkButton>

        </div>
    </ConsistentContentHolder>
</template>

<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import HorizontalLineText from "@/Components/General/HorizontalLineText.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import StudioLinkButton from "@/Components/Buttons/StudioLinkButton.vue";
import YouTubeIcon from '#icons/youtube.svg';
import TwitchIcon from '#icons/twitch.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';
const name = 'ConnectChannels';
</script>

<style scoped>

</style>
