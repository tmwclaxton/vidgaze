<template>
    <p class="font-bold text-lg">Connect your Channels to VidGaze</p>

    <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

    <div class="flex flex-row flex-wrap gap-2 ">
        <StudioLinkButton :platform="'youtube'" :external_id="useAuthStore().sources.youtube"
                          :text="!useAuthStore().sources.youtube ? 'Sign in with YouTube' : useAuthStore().sources.youtube"
                          buttonClasses="bg-red-200 hover:bg-red-300">
            <YouTubeIcon class="w-6 h-6 my-auto text-zinc-200"/>
        </StudioLinkButton>
        <StudioLinkButton platform="dailymotion" :external_id="useAuthStore().sources.dailymotion"
                          :text="!useAuthStore().sources.dailymotion ? 'Sign in with Dailymotion' : useAuthStore().sources.dailymotion"
                          buttonClasses="bg-zinc-200 hover:bg-zinc-300">
            <DailyMotionIcon class="w-6 h-6 my-auto  "/>
        </StudioLinkButton>
        <StudioLinkButton platform="twitch" :external_id="useAuthStore().sources.twitch"
                          :text="!useAuthStore().sources.twitch ? 'Sign in with Twitch' : useAuthStore().sources.twitch"
                          buttonClasses="bg-purple-200 hover:bg-purple-300">
            <TwitchIcon class="w-6 h-6 my-auto "/>
        </StudioLinkButton>
        <StudioLinkButton platform="vimeo" :external_id="useAuthStore().sources.vimeo"
                          :text="!useAuthStore().sources.vimeo ? 'Sign in with Vimeo' : useAuthStore().sources.vimeo"
                          buttonClasses="bg-blue-200 hover:bg-blue-300">
            <VimeoIcon class="w-6 h-6 my-auto "/>
        </StudioLinkButton>

    </div>
</template>

<script setup>
import StudioLinkButton from "@/Components/Buttons/StudioLinkButton.vue";
import YouTubeIcon from '#icons/youtube.svg';
import TwitchIcon from '#icons/twitch.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';
import {useAuthStore} from "@/Stores/AuthStore";

const name = 'ConnectChannels';



</script>
