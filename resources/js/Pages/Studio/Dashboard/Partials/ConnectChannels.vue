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
                          buttonClasses="bg-zinc-100 hover:bg-zinc-200">
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
        <StudioLinkButton platform="tiktok" :external_id="useAuthStore().sources.tiktok"
                          :text="!useAuthStore().sources.tiktok ? 'Sign in with TikTok' : useAuthStore().sources.tiktok"
                          buttonClasses="bg-white hover:bg-zinc-100">
            <TikTokIcon class="w-6 h-6 my-auto "/>
        </StudioLinkButton>
        <StudioLinkButton platform="facebook" :external_id="useAuthStore().sources.facebook"
                          :text="!useAuthStore().sources.facebook ? 'Sign in with FaceBook' : useAuthStore().sources.facebook"
                          buttonClasses="bg-blue-300 hover:bg-blue-400">
            <FacebookIcon class="w-6 h-6 my-auto "/>
        </StudioLinkButton>
        <StudioLinkButton platform="rumble" :external_id="useAuthStore().sources.rumble"
                          :text="!useAuthStore().sources.rumble ? 'Sign in with Rumble' : useAuthStore().sources.rumble"
                          buttonClasses="bg-green-200 hover:bg-green-300">
            <RumbleIcon class="w-6 h-6 my-auto "/>
        </StudioLinkButton>
        <StudioLinkButton platform="oydsee" :external_id="useAuthStore().sources.oydsee"
                          :text="!useAuthStore().sources.oydsee ? 'Sign in with Odysee' : useAuthStore().sources.oydsee"
                          buttonClasses="bg-red-200 hover:bg-red-300">
            <OdyseeIcon class="w-6 h-6 my-auto "/>
        </StudioLinkButton>

    </div>
</template>

<script setup>
import StudioLinkButton from "@/Components/Buttons/StudioLinkButton.vue";
import YouTubeIcon from '#icons/youtube.svg';
import TwitchIcon from '#icons/twitch.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';
import TikTokIcon from '#icons/tiktok.svg';
import RumbleIcon from '#icons/rumble.svg';
import FacebookIcon from '#icons/facebook.svg';
import OdyseeIcon from '#icons/odysee.svg';
import {useAuthStore} from "@/Stores/AuthStore";

const name = 'ConnectChannels';



</script>
