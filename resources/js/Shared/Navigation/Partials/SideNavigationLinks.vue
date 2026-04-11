<script setup>
import LivestreamIcon from '~/images/icons/live.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import ContentIcon from '~/images/icons/categories.svg';
import UploadIcon from '~/images/icons/upload.svg';
import UnionIcon from '~/images/icons/union.svg';
import CustomiseIcon from '~/images/icons/wand.svg';
import SidebarNavLink from '@/Shared/Navigation/SidebarNavLink.vue';

import HomeIcon from '~/images/icons/home.svg';
import StreamIcon from '~/images/icons/livestreams.svg';
import PodcastIcon from '~/images/icons/podcast.svg';
import SubscriptionsIcon from '~/images/icons/subscriptions.svg';
import CategoriesIcon from '~/images/icons/categories.svg';
import LibraryIcon from '~/images/icons/library.svg';
import { useNavStore } from '@/Stores/NavStore';
import { useAuthStore } from '@/Stores/AuthStore';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const navStore = useNavStore();
</script>

<template>
    <div v-if="!navStore.showingStudioLinks" class="space-y-0.5">
        <div>
            <SidebarNavLink :href="route('home')" :active="route().current('home')" label="Home">
                <template #icon="{ svgClass }">
                    <HomeIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div>
            <SidebarNavLink :href="route('streams.index')" :active="route().current('streams.index')" label="Streams">
                <template #icon="{ svgClass }">
                    <StreamIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div>
            <SidebarNavLink :href="route('category.index')" :active="route().current('category.index')" label="Categories">
                <template #icon="{ svgClass }">
                    <CategoriesIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div>
            <SidebarNavLink :href="route('podcasts.index')" :active="route().current('podcasts.index')" label="Podcasts">
                <template #icon="{ svgClass }">
                    <PodcastIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div v-if="useAuthStore().areShortsEnabled()" class="">
            <SidebarNavLink :href="route('videos.shorts')" :active="route().current('videos.shorts')" label="Shorts">
                <template #icon="{ faClass }">
                    <font-awesome-icon :icon="['fas', 'fire']" :class="faClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div v-if="useAuthStore().user != null">
            <SidebarNavLink
                :href="route('feed.subscriptions')"
                :active="route().current('feed.subscriptions')"
                label="Subscriptions"
            >
                <template #icon="{ svgClass }">
                    <SubscriptionsIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div v-if="useAuthStore().user != null">
            <SidebarNavLink :href="route('feed.library')" :active="route().current('feed.library')" label="Library">
                <template #icon="{ svgClass }">
                    <LibraryIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
    </div>

    <div v-if="navStore.showingStudioLinks" class="space-y-0.5">
        <div v-if="useAuthStore().user != null">
            <SidebarNavLink
                :href="route('studio.dashboard')"
                :active="route().current('studio.dashboard')"
                label="Dashboard"
            >
                <template #icon="{ svgClass }">
                    <ProfileIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div v-if="useAuthStore().user != null">
            <SidebarNavLink :href="route('studio.content')" :active="route().current('studio.content')" label="Content">
                <template #icon="{ svgClass }">
                    <ContentIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div v-if="useAuthStore().user != null">
            <SidebarNavLink :href="route('studio.upload')" :active="route().current('studio.upload')" label="Upload">
                <template #icon="{ svgClass }">
                    <UploadIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div v-if="useAuthStore().user != null">
            <SidebarNavLink :href="route('studio.streaming')" :active="route().current('studio.streaming')" label="Stream">
                <template #icon="{ svgClass }">
                    <LivestreamIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div v-if="useAuthStore().user != null">
            <SidebarNavLink :href="route('studio.unionise')" :active="route().current('studio.unionise')" label="Unions">
                <template #icon="{ svgClass }">
                    <UnionIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
        <div v-if="useAuthStore().user != null">
            <SidebarNavLink :href="route('studio.customise')" :active="route().current('studio.customise')" label="Customise">
                <template #icon="{ svgClass }">
                    <CustomiseIcon :class="svgClass" />
                </template>
            </SidebarNavLink>
        </div>
    </div>
</template>
