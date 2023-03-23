<script setup>
import VideoIcon from '~/images/icons/play.svg';
import LivestreamIcon from '~/images/icons/livestreams.svg';
import ShortsIcon from '~/images/icons/shorts.svg';
import PodcastsIcon from '~/images/icons/podcast.svg';
import MusicIcon from '~/images/icons/music.svg';
import NavLink from '@/Components/Links/NavLink.vue';
import ProfileIcon from '~/images/icons/profile.svg';
import ContentIcon from '~/images/icons/categories.svg';
import UploadIcon from '~/images/icons/upload.svg';
import StreamIcon from '~/images/icons/stream.svg';
import UnionIcon from '~/images/icons/union.svg';
import CustomiseIcon from '~/images/icons/wand.svg';
import PodcastIcon from '~/images/icons/podcast.svg';
import ResponsiveNavLink from "@/Components/Links/ResponsiveNavLink.vue";

//name of the component
const name = 'ExpandableNavigationLinks';

//accept props
const props = defineProps({
    showingStudioLinks: {
        type: Boolean,
        required: true
    },
})
</script>


<template>




    <div  v-if="!showingStudioLinks" class="  space-y-1 ">
<!--        <div class=" md:hidden">-->
<!--            <ResponsiveNavLink :href="route('home')" :active="route().current('home')">-->
<!--                <div class="flex flex-row items-center gap-x-2">-->
<!--                    <HomeIcon class="w-5 h-5"/>-->
<!--                    <span>Home</span>-->
<!--                </div>-->
<!--            </ResponsiveNavLink>-->
<!--        </div>-->
        <div class="md:hidden">
            <ResponsiveNavLink :href="route('videos')" :active="route().current('videos')">
                <div class="flex flex-row items-center gap-x-2">
                    <VideoIcon class="w-5 h-5"/>
                    <span>Videos</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <div class="md:hidden">
            <ResponsiveNavLink :href="route('streams')" :active="route().current('streams')">
                <div class="flex flex-row items-center gap-x-2">
                    <StreamIcon class="w-5 h-5"/>
                    <span>Streams</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <div class="md:hidden">
            <ResponsiveNavLink :href="route('shorts')" :active="route().current('shorts')">
                <div class="flex flex-row items-center gap-x-2">
                    <ShortsIcon class="w-5 h-5"/>
                    <span>Shorts</span>
                </div>
            </ResponsiveNavLink>
        </div>

        <div class="lg:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <MusicIcon class="w-5 h-5"/>
                    <span>Music</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <div class="lg:hidden">
            <ResponsiveNavLink :href="route('podcasts')" :active="route().current('podcasts')">
                <div class="flex flex-row items-center gap-x-2">
                    <PodcastIcon class="w-5 h-5"/>
                    <span>Podcasts</span>
                </div>
            </ResponsiveNavLink>
        </div>

    </div>


</template>
