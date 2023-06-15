<script setup>
import { Head } from '@inertiajs/vue3';
import Title from "@/Components/General/Title.vue";

import StreamIcon from '~/images/icons/livestreams.svg';
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import HorizontalLineText from "@/Components/General/HorizontalLineText.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import StudioLinkButton from "@/Components/Buttons/StudioLinkButton.vue";
import YouTubeIcon from '#icons/youtube.svg';
import TwitchIcon from '#icons/twitch.svg';
import DailyMotionIcon from '#icons/dailymotion.svg';
import VimeoIcon from '#icons/vimeo.svg';

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
                <ConsistentContentHolder class="p-5 h-full">
                    <div class="flex flex-col items-center justify-center">
                        <img v-bind:src="$page.props.auth.user.data.creator.avatar_url"
                             class="w-24 aspect-square rounded-full mb-3 shadow">
                        <p class="font-bold text-lg" >Your Channel</p>
                        <p class="font-light text-xl" v-text="$page.props.auth.user.data.creator.name"></p>
                    </div>

                    <div class="border-t border-zinc-200 dark:border-zinc-600 my-2"></div>

                    <div class="flex flex-col">
                        <p class="font-bold mb-1 ">VidGaze Channel Analytics</p>

                        <div class=" flex flex-row flex-wrap gap-2 w-full">
                            <ConsistentContentHolder class="flex flex-row align-middle justify-center w-max px-5">
                                <p class="text-sm" v-text="$page.props.auth.user.data.creator.subscriber_count "/>
                            </ConsistentContentHolder>
                            <ConsistentContentHolder class="flex flex-row align-middle justify-center w-max px-5">
                                <p class="text-sm" v-text="'3:53 Avg View Duration '"/>
                            </ConsistentContentHolder>
                            <ConsistentContentHolder class="flex flex-row align-middle justify-center w-max px-5">
                                <p class="text-sm" v-text="'25 Views this Month'"/>
                            </ConsistentContentHolder>
                        </div>
                    </div>
                </ConsistentContentHolder>
            </div>

            <!--connect channels-->
            <div class="col-span-4 row-span-1">
                <ConsistentContentHolder class="p-5 h-full">
                    <p class="font-bold text-lg">Connect your Channels to VidGaze</p>

                    <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

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
            </div>

            <div class="col-span-4 row-span-2">
                <ConsistentContentHolder class="p-5 h-full">

                    <p class="font-bold text-lg">Latest Video Performance</p>


                    <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

                    <div class="w-full grid grid-cols-3">
                        <div class="cols-1">
                            <img src="https://i.ytimg.com/vi/9XaS93WMRQQ/hqdefault.jpg" class="w-full aspect-[21/12] rounded-lg shadow">
                        </div>
                        <div class="col-2">

                        </div>
                    </div>

                </ConsistentContentHolder>
            </div>

            <div class="col-span-2 row-span-2">
                <ConsistentContentHolder class="p-5 h-full">

                    <p class="font-bold text-lg">Latest Comment</p>


                    <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

                    <div class="w-full grid grid-cols-3">
                        <div class="cols-1">
                            <img src="https://i.ytimg.com/vi/9XaS93WMRQQ/hqdefault.jpg" class="w-full aspect-[21/12] rounded-lg shadow">
                        </div>
                        <div class="col-2">

                        </div>
                    </div>

                </ConsistentContentHolder>
            </div>
        </div>

    </ConsistentPadding>
</template>
