<script setup>
import LivestreamIcon from '~/images/icons/live.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import ContentIcon from '~/images/icons/categories.svg';
import UploadIcon from '~/images/icons/upload.svg';
import UnionIcon from '~/images/icons/union.svg';
import CustomiseIcon from '~/images/icons/wand.svg';
import ResponsiveNavLink from "@/Components/Links/ResponsiveNavLink.vue";


import VideoIcon from '~/images/icons/play.svg';
import HomeIcon from '~/images/icons/home.svg';
import ShortsIcon from '~/images/icons/shorts.svg';
import MusicIcon from '~/images/icons/music.svg';
import StreamIcon from '~/images/icons/livestreams.svg';
import PodcastIcon from '~/images/icons/podcast.svg';
import SubscriptionsIcon from '~/images/icons/subscriptions.svg';
import LibraryIcon from '~/images/icons/library.svg';
import {useNavStore} from "@/Stores/NavStore";
import {watch} from "vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
const navStore = useNavStore();

//name of the component
const name = 'ExpandableNavigationLinks';



</script>


<template>




    <div  v-if="!navStore.showingStudioLinks" class="  space-y-1 ">
        <!--<p v-text="isStudioRoute" class="text-white"></p>-->
        <div class="ld:hid den">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')">
                <!--<VideoIcon class="w-5 h-5 flex-shrink-0"/>-->
                <HomeIcon class="w-5 h-5 flex-shrink-0"/>
                <span>Home</span>

            </ResponsiveNavLink>
        </div>
        <div class="ld:hid den">
            <ResponsiveNavLink :href="route('streams.index')" :active="route().current('streams.index')">
                <StreamIcon class="w-5 h-5 flex-shrink-0"/>
                <span>Streams</span>
            </ResponsiveNavLink>
        </div>
        <div class="" v-if="useAuthStore().areShortsEnabled()">
            <ResponsiveNavLink :href="route('videos.shorts')" :active="route().current('videos.shorts')">
                <font-awesome-icon :icon="['fas', 'fire']" class="w-5 h-5 flex-shrink-0"/>
                <span>Shorts</span>
            </ResponsiveNavLink>
        </div>
        <div class="">
            <ResponsiveNavLink :href="route('chatroom.show')" :active="route().current('chatroom.show')">
                <font-awesome-icon :icon="['fas', 'message']" class="w-5 h-5 flex-shrink-0"/>
                <span>Global Chat</span>
            </ResponsiveNavLink>
        </div>
        <div  class="2xl:hid den ">
            <ResponsiveNavLink :href="route('equity')" :active="route().current('equity')">
                <font-awesome-icon :icon="['fas', 'chart-pie']" class="w-5 h-5 flex-shrink-0"/>
                <span>Equity</span>
            </ResponsiveNavLink>
        </div>

        <!--subscriptions-->
        <div v-if="useAuthStore().user != null" class="lg:hid den">
            <ResponsiveNavLink :href="route('feed.subscriptions')" :active="route().current('feed.subscriptions')">
                <SubscriptionsIcon class="w-5 h-5 flex-shrink-0"/>
                <span>Subscriptions</span>
            </ResponsiveNavLink>
        </div>

        <!--library-->
        <div v-if="useAuthStore().user != null" class="xl:hid den">
            <ResponsiveNavLink :href="route('feed.library')" :active="route().current('feed.library')">
                <LibraryIcon class="w-5 h-5 flex-shrink-0"/>
                <span>Library</span>
            </ResponsiveNavLink>
        </div>
    </div>


    <!--studio links-->
    <div  v-if="navStore.showingStudioLinks" class="  space-y-1 ">
        <!--dashboard-->
        <div v-if="useAuthStore().user != null" class="md:hid den">
            <ResponsiveNavLink :href="route('studio.dashboard')" :active="route().current('studio.dashboard')">
                    <ProfileIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Dashboard</span>
            </ResponsiveNavLink>
        </div>
        <!--content page-->
        <div v-if="useAuthStore().user != null" class="md:hid den">
            <ResponsiveNavLink :href="route('studio.content')" :active="route().current('studio.content')">
                    <ContentIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Content</span>
            </ResponsiveNavLink>
        </div>
        <!--upload page-->
        <div v-if="useAuthStore().user != null" class="ld:hid den">
            <ResponsiveNavLink :href="route('studio.upload')" :active="route().current('studio.upload')">
                    <UploadIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Upload</span>
            </ResponsiveNavLink>
        </div>
        <!--creator streams-->
        <div v-if="useAuthStore().user != null" class="xl:hid den">
            <ResponsiveNavLink :href="route('studio.streaming')" :active="route().current('studio.streaming')">
                    <LivestreamIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Stream</span>
            </ResponsiveNavLink>
        </div>
        <!--dashboard-->
        <div v-if="useAuthStore().user != null" class="xl:hid den">
            <ResponsiveNavLink :href="route('studio.unionise')" :active="route().current('studio.unionise')">
                    <UnionIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Unions</span>
            </ResponsiveNavLink>
        </div>
        <!--Customise-->
        <div v-if="useAuthStore().user != null" class="md:hid den">
            <ResponsiveNavLink :href="route('studio.customise')" :active="route().current('studio.customise')">
                    <CustomiseIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Customise</span>
            </ResponsiveNavLink>
        </div>
    </div>

</template>
