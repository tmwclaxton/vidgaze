<script setup>
import Dropdown from '@/Components/Dropdown/Dropdown.vue';
import DropdownLink from '@/Components/Dropdown/DropdownLink.vue';

import ResponsiveNavLink from '@/Components/Links/ResponsiveNavLink.vue';
import {Link} from '@inertiajs/vue3';
import {useDark, useToggle, useWindowSize} from "@vueuse/core";


import TopNavigationLinks from '@/Shared/Navigation/TopNavigationLinks.vue';
import ExpandableNavigationLinks from "@/Shared/Navigation/ExpandableNavigationLinks.vue";

import SearchIcon from '~/images/icons/search.svg';
import OpenNavSVG from '~/images/icons/3lines.svg';
import CloseNavSVG from '~/images/icons/exit.svg';
import SunIcon from '~/images/icons/sun.svg';
import MoonIcon from '~/images/icons/moon.svg';

import LogoutIcon from '~/images/icons/logout.svg';
import StudioIcon from '~/images/icons/light.svg';
import SettingsIcon from '~/images/icons/settings.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {onMounted, onUnmounted, ref, watch} from "vue";
// import { AiCrossref } from "oh-vue-icons/icons";

const isDark = useDark();
const toggleDark = useToggle(isDark);
const expandedSearchBar = ref(false);
const props = defineProps({
    showingNavigationDropdown: {
        type: Boolean,
        required: true
    },
    showingStudioLinks: {
        type: Boolean,
        required: true
    }
});

const windowWidth = ref(window.innerWidth)

const handleResize = () => {
    windowWidth.value = window.innerWidth;
    console.log(windowWidth.value);
    if (windowWidth.value > 640) {
        expandedSearchBar.value = false;
    }
}

const toggleExpandedSearchBarOn = () => {
    if (windowWidth.value <= 640) {
        expandedSearchBar.value = true;
    }
}

const toggleExpandedSearchBarOff = () => {
        expandedSearchBar.value = false;
}

onMounted(() => {
    window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
})


</script>

<template>
    <div class="h-16">
        <!--Nav is fixed so lets space things below-->

    </div>
    <nav class=" fixed z-40 top-0 w-full " >
        <!-- Primary Navigation Menu -->
        <div class="flex flex-col" :class="{'h-screen lg:h-max': showingNavigationDropdown,
                            '': !showingNavigationDropdown,
                        }">
            <div class="bg-vidgaze-blue">
                <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8  flex flex-col">
                    <div class="flex justify-between h-16 ">
                        <div class="flex w-full">
                            <!-- Hamburger, hide whenever search icon is clicked in mobile mode -->
                            <div class=" flex items-center  "
                                 :class="
                                {
                                    'hidden sm:flex': expandedSearchBar,
                                    '': !expandedSearchBar,
                                }">
                                <button
                                    @click="showingNavigationDropdown = !showingNavigationDropdown"
                                    class="inline-flex items-center justify-center p-2 rounded-md text-zinc-400 dark:text-zinc-500 hover:text-zinc-500 dark:hover:text-zinc-400  focus:outline-none  focus:text-zinc-400 transition duration-150 ease-in-out"
                                >
                                    <OpenNavSVG class="h-6 w-6 fill-white" :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"/>
                                    <CloseNavSVG class="h-6 w-6 fill-white" :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"/>
                                </button>
                            </div>


                            <!-- Logo -->
                            <div class="shrink-0 flex items-center  md:mr-5"  :class="
                                {
                                    'hidden sm:flex': expandedSearchBar,
                                    '': !expandedSearchBar,
                                }">
                                <Link :href="route('home')">
                                    <img src="/images/logos/vidgaze/vidgaze_banner.png" alt="VidGaze Logo"
                                         class="h-10 w-auto">
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <TopNavigationLinks :showingStudioLinks="showingStudioLinks"/>


                            <!--Search bar-->
                            <div class="flex flex-col flex-grow  justify-center items-end sm:items-center sm:px-5">
                                <div
                                    class="flex flex-row space-x-3 w-full justify-end sm:justify-center">

                                    <div class="p-2 pl-1  " :class="{
                                            hidden: !expandedSearchBar,
                                            ' flex': expandedSearchBar,
                                        }">
                                        <!--Exit expanded search-->
                                        <CloseNavSVG @click="toggleExpandedSearchBarOff" class="w-7 aspect-square flex-shrink-0 text-white inline-flex my-auto" />
                                    </div>

                                    <div :class="
                                            {
                                                'w-full flex-row-reverse': expandedSearchBar,
                                                ' w-max sm:w-full max-w-md flex-row-reverse ': !expandedSearchBar,
                                            }"
                                         class="flex sm:gap-x-2 items-center text-zinc-500 p-2 px-3 rounded-xl bg-zinc-900">
                                    <SearchIcon @click="toggleExpandedSearchBarOn" class="w-5 h-5 flex-shrink-0"  />
                                    <input type="text"
                                           class="   bg-transparent p-0 m-0 without-ring placeholder-zinc-500 text-white"
                                           :class="
                                            {
                                                'w-full': expandedSearchBar,
                                                'w-0 sm:w-full': !expandedSearchBar,
                                            }"
                                           placeholder="Search YouTube, Twitch and more...">
                                    </div>

                                </div>


                            </div>

                            <!--log in-->
                            <div v-if="$page.props.auth.user == null"
                                 class="hidden lg:flex sm:items-center   flex-shrink-0">
                                <div class="flex gap-x-2">
                                    <Link :href="route('login')" class="text-sm text-zinc-300 hover:text-zinc-400">
                                        Log In
                                    </Link>
                                    <p class="text-sm text-zinc-300">/</p>
                                    <Link :href="route('register')" class="text-sm text-zinc-300 hover:text-zinc-400">
                                        Sign Up
                                    </Link>
                                </div>
                            </div>

                        </div>

                        <div v-if="$page.props.auth.user != null" class=" flex  items-center   flex-shrink-0">
                            <!-- Profile Dropdown -->
                            <div class="relative hidden sm:flex">
                                <Dropdown align="right" width="56" distance="1.5">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md   ">
                                            <button
                                                type="button"
                                                class="inline-flex items-center h-full border border-transparent  rounded-md bg-transparent hover:text-zinc-300 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                <img class="h-9 aspect-square rounded-full bg-zinc-800 aspect-square  "
                                                     v-bind:src="$page.props.auth.creator.avatar_url">

                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')" class="flex flex-row space-x-2">
                                            <!--Profile picture-->
                                            <img class="h-9 aspect-square rounded-full bg-zinc-800 aspect-square  "
                                                 v-bind:src="$page.props.auth.creator.avatar_url">
                                            <div class="flex flex-col">
                                                <span class="text-white font-bold">{{$page.props.auth.user.email}}</span>
                                                <span class="text-blue-500 font-bold">Manage your account</span>
                                            </div>




                                        </DropdownLink>
                                        <DropdownLink :href="route('profile.edit')" class="flex flex-row space-x-2">
                                            <ProfileIcon class="w-5 h-5 flex-shrink-0" />
                                            <span class="font-bold">Your Channel</span>
                                        </DropdownLink>
                                        <DropdownLink :href="route('studio.dashboard')" class="flex flex-row space-x-2">
                                            <StudioIcon class="w-5 h-5 flex-shrink-0" />
                                            <span class="font-bold">VidGaze Studio</span>
                                        </DropdownLink>
                                        <DropdownLink :href="route('profile.edit')" class="flex flex-row space-x-2">
                                            <!--<SettingsIcon class="w-5 h-5 flex-shrink-0" />-->
                                            <span class="font-bold">VidCoins</span>
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button" class="flex flex-row space-x-2">
                                            <LogoutIcon class="w-5 h-5 flex-shrink-0" />
                                            <span class="font-bold">Log Out</span>
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>


                    </div>
                </div>

            </div>





            <!-- Responsive Navigation Menu -->
            <div class=" bg-vidgaze-blue w-full flex flex-row flex-grow ">
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 pb-2 pt-2   flex flex-col justify-between flex-grow    "
                >

                    <div id="top" class="">
                        <ExpandableNavigationLinks :showingStudioLinks="showingStudioLinks"/>

                        <div class="border-t border-zinc-600 my-1 "></div>
                        <div class="">

                            <div v-if="$page.props.auth.user != null" class="space-y-1 sm:hidden">
                                <ResponsiveNavLink :href="route('profile.edit')">
                                    <div class="flex flex-row items-center gap-x-2">
                                        <SettingsIcon class="w-5 h-5"/>
                                        <span>Manage Your Account</span>
                                    </div>
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('profile.edit')">
                                    <div class="flex flex-row items-center gap-x-2">
                                        <ProfileIcon class="w-5 h-5"/>
                                        <span>Your Channel</span>
                                    </div>
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('studio.dashboard')">
                                    <div class="flex flex-row items-center gap-x-2">
                                        <StudioIcon class="w-5 h-5"/>
                                        <span>VidGaze Studio</span>
                                    </div>
                                </ResponsiveNavLink>
                            </div>


                            <!-- Responsive Settings Options -->
                            <div class="lg:hidden" v-if="$page.props.auth.user != null">


                                <div class="mt-1 space-y-1">

                                    <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                        <div class="flex flex-row items-center gap-x-2">
                                            <LogoutIcon class="w-5 h-5"/>
                                            <span>Log Out</span>
                                        </div>
                                    </ResponsiveNavLink>
                                </div>
                            </div>
                            <div v-else class="lg:hidden">
                                <div class="mt-1 space-y-1">
                                    <!--                            <ResponsiveNavLink :href="route('login')"> Log In </ResponsiveNavLink>-->
                                    <ResponsiveNavLink :href="route('register')">
                                        <div class="flex flex-row items-center gap-x-2">
                                            <ProfileIcon class="w-5 h-5"/>
                                            <span>Sign Up</span>
                                        </div>
                                    </ResponsiveNavLink>
                                </div>
                            </div>
                        </div>


                        <!--Everything below here should always be on the screen-->


                        <!--                dark/light mode-->
                        <div class="text-white cursor-pointer space-y-1" @click="toggleDark()">
                    <span v-if="!isDark">
                    <ResponsiveNavLink span="true">
                        <div class="flex flex-row items-center gap-x-2">
                            <SunIcon class="w-5 h-5"/>
                            <span>Light Mode</span>
                        </div>
                    </ResponsiveNavLink>
                    </span>
                            <span v-else>

                        <ResponsiveNavLink span="true">
                            <div v-if="isDark" class="flex flex-row items-center gap-x-2">
                                <MoonIcon class="w-5 h-5"/>
                                <span class="">Dark Mode</span>
                            </div>
                        </ResponsiveNavLink>
                    </span>
                        </div>
                    </div>


                    <div id="bottom" class=" pb-1">

                        <!--add about page-->
                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('about')">
                                <div class="flex flex-row items-center gap-x-2">
                                    <font-awesome-icon :icon="['fas', 'heart']"/>
                                    <span>About</span>
                                </div>
                            </ResponsiveNavLink>
                        </div>
                        <!--add support page-->
                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('about')">
                                <div class="flex flex-row items-center gap-x-2">
                                    <font-awesome-icon :icon="['fass', 'phone']"/>
                                    <span>Support</span>
                                </div>
                            </ResponsiveNavLink>
                        </div>
                        <!--add policy page-->
                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('about')">
                                <div class="flex flex-row items-center gap-x-2">
                                    <font-awesome-icon :icon="['fass', 'scroll']"/>
                                    <span>Privacy Policy</span>
                                </div>
                            </ResponsiveNavLink>
                        </div>
                        <!--add terms page-->
                        <div class="space-y-1">
                            <ResponsiveNavLink :href="route('about')">
                                <div class="flex flex-row items-center gap-x-2">
                                    <font-awesome-icon :icon="['fass', 'asterisk']"/>
                                    <span>Terms of Service</span>
                                </div>
                            </ResponsiveNavLink>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </nav>

</template>

