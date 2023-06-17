<script setup>
import { Head } from '@inertiajs/vue3';
import Title from "@/Components/General/Title.vue";

import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import StudioLinkButton from "@/Components/Buttons/StudioLinkButton.vue";
import YouTubeIcon from '#icons/youtube.svg';
import TwitchIcon from '#icons/twitch.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';
import ConnectChannels from "@/Pages/Studio/Partials/ConnectChannels.vue";
import ItemPerformance from "@/Pages/Studio/Partials/ItemPerformance.vue";
import LatestComment from "@/Pages/Studio/Partials/LatestComment.vue";
import ChannelOverview from "@/Pages/Studio/Partials/ChannelOverview.vue";
import {defineProps} from "vue";

let props = defineProps({
    claimed_platforms: {
        type: Object,
        required: true
    }
})

</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,

};

</script>

<template>
    <Head title="Studio Dashboard" />

    <ConsistentPadding class="-mt-4">
        <Title :text="'Channel Dashboard'">
            <StreamIcon class="w-6 h-6 my-auto"/>
        </Title>

        <div class="grid grid-cols-6 grid-rows-6 gap-4 -mt-2">
            <!--channel overview -->
            <div class="col-span-2 row-span-2">
                <ChannelOverview/>
            </div>

            <!--connect channels-->
            <div class="col-span-4 row-span-1">
                <ConsistentContentHolder class="p-5 h-full">
                    <p class="font-bold text-lg">Connect your Channels to VidGaze</p>

                    <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

                    <div class="flex flex-row flex-wrap gap-2 ">
                        <StudioLinkButton v-if="!claimed_platforms.youtube" platform="youtube" text="Sign in with YouTube" buttonClasses="bg-red-200 hover:bg-red-300">
                            <YouTubeIcon class="w-6 h-6 my-auto text-zinc-200"/>
                        </StudioLinkButton>
                        <StudioLinkButton v-if="!claimed_platforms.dailymotion" platform="dailymotion" text="Sign in with Dailymotion" buttonClasses="bg-zinc-200 hover:bg-zinc-300">
                            <DailyMotionIcon class="w-6 h-6 my-auto  "/>
                        </StudioLinkButton>
                        <StudioLinkButton v-if="!claimed_platforms.twitch" platform="twitch" text="Sign in with Twitch" buttonClasses="bg-purple-200 hover:bg-purple-300">
                            <TwitchIcon class="w-6 h-6 my-auto "/>
                        </StudioLinkButton>
                        <StudioLinkButton v-if="!claimed_platforms.vimeo" platform="vimeo" text="Sign in with Vimeo" buttonClasses="bg-blue-200 hover:bg-blue-300">
                            <VimeoIcon class="w-6 h-6 my-auto "/>
                        </StudioLinkButton>

                    </div>
                </ConsistentContentHolder>
                <ConnectChannels/>
            </div>


            <!--latest item performance-->
            <div class="col-span-4 row-span-2">
                <ItemPerformance/>
            </div>

            <div class="col-span-2 row-span-2">
                <LatestComment/>
            </div>
        </div>

    </ConsistentPadding>
</template>
