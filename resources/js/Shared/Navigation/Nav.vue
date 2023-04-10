<script setup>

import ResponsiveNavLink from '@/Components/Links/ResponsiveNavLink.vue';
import {Link} from '@inertiajs/vue3';
import {useDark, useToggle} from "@vueuse/core";


import TopNavigationLinks from '@/Shared/Navigation/TopNavigationLinks.vue';
import ExpandableNavigationLinks from "@/Shared/Navigation/ExpandableNavigationLinks.vue";
import ProfileDropdown from "@/Components/Dropdown/ProfileDropdown.vue";
import UploadDropdown from "@/Components/Dropdown/UploadDropdown.vue";
import NotificationsDropdown from "@/Components/Dropdown/NotificationsDropdown.vue";

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


// this is for the search bar so when you resize the window it will close the expanded search bar
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
    <nav class=" fixed z-40 top-0 w-full ">
        <!-- Primary Navigation Menu -->
        <div class="flex flex-col" :class="{'h-screen lg:h-max': showingNavigationDropdown,
                            '': !showingNavigationDropdown,
                        }">
            <div class="bg-vidgaze-blue-nav">
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
                            <div class="shrink-0 flex items-center  md:mr-5" :class="
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
                            <div v-if="!showingStudioLinks"
                                 class="flex flex-col flex-grow  justify-center items-end sm:items-center sm:px-5">
                                <div
                                    class="relative flex flex-row space-x-3 w-full justify-end sm:justify-center">

                                    <div class="p-2 pl-1  " :class="{
                                            hidden: !expandedSearchBar,
                                            ' flex': expandedSearchBar,
                                        }">
                                        <!--Exit expanded search-->
                                        <CloseNavSVG @click="toggleExpandedSearchBarOff"
                                                     class="w-7 aspect-square flex-shrink-0 text-white inline-flex my-auto"/>
                                    </div>

                                    <div :class="
                                            {
                                                'w-full flex-row-reverse': expandedSearchBar,
                                                ' w-max sm:w-full max-w-md flex-row-reverse ': !expandedSearchBar,
                                            }"
                                         class="relative flex sm:gap-x-2 items-center text-zinc-500 p-2 px-3 rounded-xl bg-zinc-900">
                                        <SearchIcon @click="toggleExpandedSearchBarOn" class="w-5 h-5 flex-shrink-0"/>
                                        <input type="text"
                                               class="   bg-transparent p-0 m-0 without-ring placeholder-zinc-500 text-white font-bold text-sm"
                                               :class="
                                            {
                                                'w-full': expandedSearchBar,
                                                'w-0 sm:w-full': !expandedSearchBar,
                                            }"
                                               placeholder="Search YouTube, Twitch and more...">

                                        <!--Search dropdown-->
                                        <div
                                            :class="{'w-full': expandedSearchBar,' w-max sm:w-full max-w-md': !expandedSearchBar}"
                                            class="absolute left-0 top-16 w-full pr-11 pl-9 sm:pl-0">
                                            <div class="relative w-full  bg-vidgaze-blue dark:bg-zinc-900 border border-zinc-900
                                            py-2 px-3 rounded-xl text-white">
                                                <div
                                                    class="relative w-full fixed pointer-events absolute rounded-none inset-x-0 mx-auto z-20 ">

                                                    <div
                                                        class=" w-full text-sm text-left flex flex-col space-y-1">
                                                            <Link href="/" class="search-suggestion" >
                                                                <div class="   overflow-x-hidden hover:bg-zinc-800 rounded-md ease-in-out duration-400 transition">
                                                                    <div scope="row" class="h-8 overflow-y-hidden flex px-3 py-2 text-base font-medium text text-white ">
                                                                        <div class="flex-shrink-0 w-4 mr-3 my-auto flex flex-col justify-center items-center">
                                                                            <SearchIcon class="w-4 h-4"/>
                                                                        </div>

                                                                        <div class="line-clamp-1  overflow-y-hidden  flex flex-col justify-center items-center ">
                                                                            <p class="font-semibold text-left leading-4 my-auto  line-clamp-1 break-words ">
                                                                                 Pewdiepie
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </Link>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>


                            </div>

                            <!--log in-->
                            <div v-if="$page.props.auth.user == null"
                                 class="hidden lg:flex sm:items-center   flex-shrink-0">
                                <div class="flex gap-x-2">
                                    <Link :href="route('login')" >
                                        <button class="text-sm capitalize bg-zinc-900 hover:bg-zinc-800 text-white p-2 px-5 rounded-md font-bold">
                                                Log In
                                        </button>
                                    </Link>
                                    <Link :href="route('register')" >
                                        <button class="text-sm capitalize bg-zinc-900 hover:bg-zinc-800 text-white p-2 px-5 rounded-md font-bold">
                                            Sign Up
                                        </button>

                                    </Link>
                                </div>
                            </div>

                        </div>

                        <div v-if="$page.props.auth.user != null" class=" flex flex-row space-x-5 items-center   flex-shrink-0">
                            <!--Upload Dropdown-->
                            <UploadDropdown/>

                            <!--Notifications Dropdown-->
                            <NotificationsDropdown/>

                            <!-- Profile Dropdown -->
                            <ProfileDropdown/>
                        </div>


                    </div>
                </div>

            </div>


            <!-- Responsive Navigation Menu -->
            <div class=" bg-vidgaze-blue-nav w-full flex flex-row flex-grow ">
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
                                    <!-- <ResponsiveNavLink :href="route('login')"> Log In </ResponsiveNavLink>-->
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

