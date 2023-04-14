<script setup>
import LivestreamIcon from '~/images/icons/live.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import ContentIcon from '~/images/icons/categories.svg';
import UploadIcon from '~/images/icons/upload.svg';
import UnionIcon from '~/images/icons/union.svg';
import CustomiseIcon from '~/images/icons/wand.svg';
import ResponsiveNavLink from "@/Components/Links/ResponsiveNavLink.vue";


import VideoIcon from '~/images/icons/play.svg';
import ShortsIcon from '~/images/icons/shorts.svg';
import MusicIcon from '~/images/icons/music.svg';
import StreamIcon from '~/images/icons/livestreams.svg';
import PodcastIcon from '~/images/icons/podcast.svg';
import SubscriptionsIcon from '~/images/icons/subscriptions.svg';
import LibraryIcon from '~/images/icons/library.svg';
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
        <div class="ld:hidden">
            <ResponsiveNavLink :href="route('videos')" :active="route().current('videos')">
                <div class="flex flex-row items-center gap-x-2">
                    <VideoIcon class="w-5 h-5"/>
                    <span>Videos</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <div class="ld:hidden">
            <ResponsiveNavLink :href="route('streams')" :active="route().current('streams')">
                <div class="flex flex-row items-center gap-x-2">
                    <StreamIcon class="w-5 h-5"/>
                    <span>Streams</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <div class="">
            <ResponsiveNavLink :href="route('shorts')" :active="route().current('shorts')">
                <div class="flex flex-row items-center gap-x-2">
                    <ShortsIcon class="w-5 h-5"/>
                    <span>Shorts</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <div class="2xl:hidden ">
            <ResponsiveNavLink :href="route('podcasts')" :active="route().current('podcasts')">
                <div class="flex flex-row items-center gap-x-2">
                    <PodcastIcon class="w-5 h-5"/>
                    <span>Podcasts</span>
                </div>
            </ResponsiveNavLink>
        </div>

        <div class=" ">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <MusicIcon class="w-5 h-5"/>
                    <span>Music</span>
                </div>
            </ResponsiveNavLink>
        </div>

        <!--subscriptions-->

        <div v-if="$page.props.auth.user != null" class="lg:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <SubscriptionsIcon class="w-5 h-5"/>
                    <span>Subscriptions</span>
                </div>
            </ResponsiveNavLink>
        </div>

        <!--library-->
        <div v-if="$page.props.auth.user != null" class="xl:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <LibraryIcon class="w-5 h-5"/>
                    <span>Library</span>
                </div>
            </ResponsiveNavLink>
        </div>
    </div>


    <!--studio links-->
    <div  v-if="showingStudioLinks" class="  space-y-1 ">
        <!--dashboard-->
        <div v-if="$page.props.auth.user != null" class="md:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <ProfileIcon class=" w-5 h-5"/>
                    <span>Dashboard</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <!--dashboard-->
        <div v-if="$page.props.auth.user != null" class="md:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <ContentIcon class=" w-5 h-5"/>
                    <span>Content</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <!--dashboard-->
        <div v-if="$page.props.auth.user != null" class="ld:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <UploadIcon class=" w-5 h-5"/>
                    <span>Upload</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <!--dashboard-->
        <div v-if="$page.props.auth.user != null" class="xl:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <LivestreamIcon class=" w-5 h-5"/>
                    <span>Stream</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <!--dashboard-->
        <div v-if="$page.props.auth.user != null" class="xl:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <UnionIcon class=" w-5 h-5"/>
                    <span>Unions</span>
                </div>
            </ResponsiveNavLink>
        </div>
        <!--Customise-->
        <div v-if="$page.props.auth.user != null" class="md:hidden">
            <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row items-center gap-x-2">
                    <CustomiseIcon class=" w-5 h-5"/>
                    <span>Customise</span>
                </div>
            </ResponsiveNavLink>
        </div>
    </div>

</template>
