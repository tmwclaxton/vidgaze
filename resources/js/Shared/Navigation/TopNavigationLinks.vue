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
import SubscriptionsIcon from '~/images/icons/subscriptions.svg';
import LibraryIcon from '~/images/icons/library.svg';

//name of the component
const name = 'TopNavigationLinks';

//accept props
const props = defineProps({
    showingStudioLinks: {
        type: Boolean,
        required: true
    },
})
</script>

<template>
    <div v-if="!showingStudioLinks" class="hidden md:flex gap-x-8 sm:-my-px text-md font-medium ">
        <NavLink :href="route('videos')" :active="route().current('videos')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <VideoIcon class=" w-4 h-4"/>
                <span>Videos</span>
            </div>
        </NavLink>
        <NavLink :href="route('streams')" :active="route().current('streams')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <LivestreamIcon class=" w-5 h-5"/>
                <span>Live</span>
            </div>
        </NavLink>
        <!--<NavLink :href="route('shorts')" :active="route().current('shorts')">-->
        <!--    <div class="flex flex-row gap-x-2 px-1 items-center">-->
        <!--        <ShortsIcon class=" w-5 h-5"/>-->
        <!--        <span>Shorts</span>-->
        <!--    </div>-->
        <!--</NavLink>-->
        <span class="hidden xl:flex ">
            <NavLink :href="route('podcasts')" :active="route().current('podcasts')">
                <div class="flex flex-row gap-x-2 px-1 items-center">
                    <PodcastsIcon class=" w-5 h-5"/>
                    <span>Podcasts</span>
                </div>
            </NavLink>
        </span>

        <span class="hidden  ">
            <NavLink :href="route('music')" :active="route().current('music')">
                <div class="flex flex-row gap-x-2 px-1 items-center">
                    <MusicIcon class=" w-5 h-5"/>
                    <span>Music</span>
                </div>
            </NavLink>
        </span>

        <NavLink v-if="$page.props.auth.user != null" class="hidden lg:flex" :href="route('streams')" :active="route().current('streams')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <SubscriptionsIcon class=" w-5 h-5"/>
                <span>Subscriptions</span>
            </div>
        </NavLink>
        <NavLink v-if="$page.props.auth.user != null" class="hidden xl:flex" :href="route('shorts')" :active="route().current('shorts')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <LibraryIcon class=" w-5 h-5"/>
                <span>Library</span>
            </div>
        </NavLink>



    </div>
    <div v-if="showingStudioLinks" class="hidden md:flex gap-x-8 sm:-my-px  text-md font-medium ">
        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <ProfileIcon class=" w-5 h-5"/>
                <span>Dashboard</span>
            </div>
        </NavLink>
        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <ContentIcon class=" w-5 h-5"/>
                <span>Content</span>
            </div>
        </NavLink>
        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <UploadIcon class=" w-5 h-5"/>
                <span>Upload</span>
            </div>
        </NavLink>
        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <StreamIcon class=" w-5 h-5"/>
                <span>Stream</span>
            </div>
        </NavLink>
        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <UnionIcon class=" w-5 h-5"/>
                <span>Unions</span>
            </div>
        </NavLink>
        <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
            <div class="flex flex-row gap-x-2 px-1 items-center">
                <CustomiseIcon class=" w-5 h-5"/>
                <span>Customise</span>
            </div>
        </NavLink>
    </div>
</template>



