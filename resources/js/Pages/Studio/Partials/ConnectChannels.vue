<template>
    <p class="font-bold text-lg">Connect your Channels to VidGaze</p>

    <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

    <div class="flex flex-row flex-wrap gap-2 ">
        <StudioLinkButton v-if="!checkIfPlatformIsClaimed('YouTube')" :platform="'youtube'" text="Sign in with YouTube" buttonClasses="bg-red-200 hover:bg-red-300">
            <YouTubeIcon class="w-6 h-6 my-auto text-zinc-200"/>
        </StudioLinkButton>
        <StudioLinkButton v-if="!checkIfPlatformIsClaimed('Dailymotion')" platform="dailymotion" text="Sign in with Dailymotion" buttonClasses="bg-zinc-200 hover:bg-zinc-300">
            <DailyMotionIcon class="w-6 h-6 my-auto  "/>
        </StudioLinkButton>
        <StudioLinkButton v-if="!checkIfPlatformIsClaimed('Twitch')" platform="twitch" text="Sign in with Twitch" buttonClasses="bg-purple-200 hover:bg-purple-300">
            <TwitchIcon class="w-6 h-6 my-auto "/>
        </StudioLinkButton>
        <StudioLinkButton v-if="!checkIfPlatformIsClaimed('Vimeo')" platform="vimeo" text="Sign in with Vimeo" buttonClasses="bg-blue-200 hover:bg-blue-300">
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

const name = 'ConnectChannels';

const props = defineProps({
    claimed_platforms: {
        type: Array,
        required: false,
        default: () => []
    }
});

const checkIfPlatformIsClaimed = (platform) => {
    return props.claimed_platforms.includes(platform);
}

</script>
