<script setup>
import LivestreamIcon from '~/images/icons/live.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import ContentIcon from '~/images/icons/categories.svg';
import UploadIcon from '~/images/icons/upload.svg';
import UnionIcon from '~/images/icons/union.svg';
import CustomiseIcon from '~/images/icons/wand.svg';
import ResponsiveNavLink from '@/Components/Links/ResponsiveNavLink.vue';

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

const name = 'ExpandableNavigationLinks';

/** Gradient label (neon family, similar to auth CTAs). */
const label = (from, to) =>
    `font-medium bg-gradient-to-r ${from} ${to} bg-clip-text text-transparent transition-all duration-200 group-hover:brightness-110`;
</script>

<template>
    <div v-if="!navStore.showingStudioLinks" class="space-y-0.5">
        <div class="ld:hidden">
            <ResponsiveNavLink :href="route('home')" :active="route().current('home')">
                <HomeIcon
                    class="h-5 w-5 shrink-0 fill-current text-sky-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(56,189,248,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(56,189,248,0.9)]"
                />
                <span :class="label('from-sky-300', 'to-cyan-400')">Home</span>
            </ResponsiveNavLink>
        </div>
        <div class="ld:hidden">
            <ResponsiveNavLink :href="route('streams.index')" :active="route().current('streams.index')">
                <StreamIcon
                    class="h-5 w-5 shrink-0 fill-current text-rose-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(251,113,133,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(251,113,133,0.85)]"
                />
                <span :class="label('from-rose-300', 'to-orange-400')">Streams</span>
            </ResponsiveNavLink>
        </div>
        <div class="ld:hidden">
            <ResponsiveNavLink :href="route('category.index')" :active="route().current('category.index')">
                <CategoriesIcon
                    class="h-5 w-5 shrink-0 fill-current text-violet-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(167,139,250,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(192,132,252,0.85)]"
                />
                <span :class="label('from-violet-300', 'to-fuchsia-400')">Categories</span>
            </ResponsiveNavLink>
        </div>
        <div class="hidden 2xl:block">
            <ResponsiveNavLink :href="route('podcasts.index')" :active="route().current('podcasts.index')">
                <PodcastIcon
                    class="h-5 w-5 shrink-0 fill-current text-pink-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(244,114,182,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(244,114,182,0.85)]"
                />
                <span :class="label('from-pink-300', 'to-purple-400')">Podcasts</span>
            </ResponsiveNavLink>
        </div>
        <div v-if="useAuthStore().areShortsEnabled()" class="">
            <ResponsiveNavLink :href="route('videos.shorts')" :active="route().current('videos.shorts')">
                <font-awesome-icon
                    :icon="['fas', 'fire']"
                    class="h-5 w-5 shrink-0 text-amber-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(251,191,36,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(251,191,36,0.9)]"
                />
                <span :class="label('from-amber-300', 'to-orange-500')">Shorts</span>
            </ResponsiveNavLink>
        </div>
        <div v-if="useAuthStore().user != null" class="lg:hidden">
            <ResponsiveNavLink
                :href="route('feed.subscriptions')"
                :active="route().current('feed.subscriptions')"
            >
                <SubscriptionsIcon
                    class="h-5 w-5 shrink-0 fill-current text-indigo-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(129,140,248,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(129,140,248,0.85)]"
                />
                <span :class="label('from-indigo-300', 'to-blue-400')">Subscriptions</span>
            </ResponsiveNavLink>
        </div>
        <div v-if="useAuthStore().user != null" class="xl:hidden">
            <ResponsiveNavLink :href="route('feed.library')" :active="route().current('feed.library')">
                <LibraryIcon
                    class="h-5 w-5 shrink-0 fill-current text-emerald-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(52,211,153,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(52,211,153,0.85)]"
                />
                <span :class="label('from-emerald-300', 'to-teal-400')">Library</span>
            </ResponsiveNavLink>
        </div>
    </div>

    <div v-if="navStore.showingStudioLinks" class="space-y-0.5">
        <div v-if="useAuthStore().user != null" class="md:hidden">
            <ResponsiveNavLink :href="route('studio.dashboard')" :active="route().current('studio.dashboard')">
                <ProfileIcon
                    class="h-5 w-5 shrink-0 fill-current text-cyan-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(34,211,238,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(34,211,238,0.85)]"
                />
                <span :class="label('from-cyan-300', 'to-teal-400')">Dashboard</span>
            </ResponsiveNavLink>
        </div>
        <div v-if="useAuthStore().user != null" class="md:hidden">
            <ResponsiveNavLink :href="route('studio.content')" :active="route().current('studio.content')">
                <ContentIcon
                    class="h-5 w-5 shrink-0 fill-current text-violet-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(167,139,250,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(167,139,250,0.85)]"
                />
                <span :class="label('from-violet-300', 'to-fuchsia-400')">Content</span>
            </ResponsiveNavLink>
        </div>
        <div v-if="useAuthStore().user != null" class="ld:hidden">
            <ResponsiveNavLink :href="route('studio.upload')" :active="route().current('studio.upload')">
                <UploadIcon
                    class="h-5 w-5 shrink-0 fill-current text-lime-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(163,230,53,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(163,230,53,0.85)]"
                />
                <span :class="label('from-lime-300', 'to-emerald-400')">Upload</span>
            </ResponsiveNavLink>
        </div>
        <div v-if="useAuthStore().user != null" class="xl:hidden">
            <ResponsiveNavLink :href="route('studio.streaming')" :active="route().current('studio.streaming')">
                <LivestreamIcon
                    class="h-5 w-5 shrink-0 fill-current text-red-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(248,113,113,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(248,113,113,0.85)]"
                />
                <span :class="label('from-red-300', 'to-rose-500')">Stream</span>
            </ResponsiveNavLink>
        </div>
        <div v-if="useAuthStore().user != null" class="xl:hidden">
            <ResponsiveNavLink :href="route('studio.unionise')" :active="route().current('studio.unionise')">
                <UnionIcon
                    class="h-5 w-5 shrink-0 fill-current text-amber-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(251,191,36,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(251,191,36,0.85)]"
                />
                <span :class="label('from-amber-300', 'to-yellow-400')">Unions</span>
            </ResponsiveNavLink>
        </div>
        <div v-if="useAuthStore().user != null" class="md:hidden">
            <ResponsiveNavLink :href="route('studio.customise')" :active="route().current('studio.customise')">
                <CustomiseIcon
                    class="h-5 w-5 shrink-0 fill-current text-fuchsia-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(232,121,249,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(232,121,249,0.85)]"
                />
                <span :class="label('from-fuchsia-300', 'to-pink-400')">Customise</span>
            </ResponsiveNavLink>
        </div>
    </div>
</template>
