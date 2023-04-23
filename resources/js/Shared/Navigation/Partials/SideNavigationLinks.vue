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
//name of the component
const name = 'ExpandableNavigationLinks';

//accept props
const props = defineProps({
    showingStudioLinks: {
        type: Boolean,
        required: true
    },
    showingNavigationDropdown: {
        type: Boolean,
        required: true
    }
})
</script>


<template>




    <div  v-if="!showingStudioLinks" class="  space-y-1 ">
        <!--<p v-text="showingNavigationDropdown" class="text-white"></p>-->
        <div class="ld:hid den">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')"
                   :showingNavigationDropdown="showingNavigationDropdown">
                    <!--<VideoIcon class="w-5 h-5 flex-shrink-0"/>-->
                    <HomeIcon class="w-5 h-5 flex-shrink-0"/>
                    <span>Home</span>

            </ResponsiveNavLink>
        </div>
        <div class="ld:hid den">
            <ResponsiveNavLink :href="route('streams.index')" :active="route().current('streams.index')"
                       :showingNavigationDropdown="showingNavigationDropdown">
                    <StreamIcon class="w-5 h-5 flex-shrink-0"/>
                    <span>Streams</span>
            </ResponsiveNavLink>
        </div>
        <div class="">
            <ResponsiveNavLink :href="route('videos.shorts')" :active="route().current('videos.shorts')"
                              :showingNavigationDropdown="showingNavigationDropdown">
                    <ShortsIcon class="w-5 h-5 flex-shrink-0"/>
                    <span>Shorts</span>
            </ResponsiveNavLink>
        </div>
        <div class="2xl:hid den ">
            <ResponsiveNavLink :href="route('podcasts')" :active="route().current('podcasts')"
                              :showingNavigationDropdown="showingNavigationDropdown">
                    <PodcastIcon class="w-5 h-5 flex-shrink-0"/>
                    <span>Podcasts</span>
            </ResponsiveNavLink>
        </div>

        <div class=" ">
            <ResponsiveNavLink :href="route('music.index')" :active="route().current('music.index')"
                      :showingNavigationDropdown="showingNavigationDropdown">
                    <MusicIcon class="w-5 h-5 flex-shrink-0"/>
                    <span>Music</span>
            </ResponsiveNavLink>
        </div>

        <!--subscriptions-->

        <div v-if="$page.props.auth.user != null" class="lg:hid den">
            <ResponsiveNavLink :href="route('feed.subscriptions')" :active="route().current('feed.subscriptions')"
                          :showingNavigationDropdown="showingNavigationDropdown">
                    <SubscriptionsIcon class="w-5 h-5 flex-shrink-0"/>
                    <span>Subscriptions</span>
            </ResponsiveNavLink>
        </div>

        <!--library-->
        <div v-if="$page.props.auth.user != null" class="xl:hid den">
            <ResponsiveNavLink :href="route('feed.library')" :active="route().current('feed.library')"
                          :showingNavigationDropdown="showingNavigationDropdown">
                    <LibraryIcon class="w-5 h-5 flex-shrink-0"/>
                    <span>Library</span>
            </ResponsiveNavLink>
        </div>
    </div>


    <!--studio links-->
    <div  v-if="showingStudioLinks" class="  space-y-1 ">
        <!--dashboard-->
        <div v-if="$page.props.auth.user != null" class="md:hid den">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')"
                          :showingNavigationDropdown="showingNavigationDropdown">
                    <ProfileIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Dashboard</span>
            </ResponsiveNavLink>
        </div>
        <!--content page-->
        <div v-if="$page.props.auth.user != null" class="md:hid den">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')"
                      :showingNavigationDropdown="showingNavigationDropdown">
                    <ContentIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Content</span>
            </ResponsiveNavLink>
        </div>
        <!--upload page-->
        <div v-if="$page.props.auth.user != null" class="ld:hid den">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')"
                              :showingNavigationDropdown="showingNavigationDropdown">
                    <UploadIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Upload</span>
            </ResponsiveNavLink>
        </div>
        <!--creator streams-->
        <div v-if="$page.props.auth.user != null" class="xl:hid den">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')"
                              :showingNavigationDropdown="showingNavigationDropdown">
                    <LivestreamIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Stream</span>
            </ResponsiveNavLink>
        </div>
        <!--dashboard-->
        <div v-if="$page.props.auth.user != null" class="xl:hid den">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')"
                              :showingNavigationDropdown="showingNavigationDropdown">
                    <UnionIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Unions</span>
            </ResponsiveNavLink>
        </div>
        <!--Customise-->
        <div v-if="$page.props.auth.user != null" class="md:hid den">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')"
                              :showingNavigationDropdown="showingNavigationDropdown">
                    <CustomiseIcon class=" w-5 h-5 flex-shrink-0"/>
                    <span>Customise</span>
            </ResponsiveNavLink>
        </div>
    </div>

</template>
