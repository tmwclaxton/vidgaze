<script setup>
import Dropdown from '@/Components/Dropdown/Dropdown.vue';
import DropdownLink from '@/Components/Dropdown/DropdownLink.vue';

import ResponsiveNavLink from '@/Components/Links/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import NavigationLinks from '@/Shared/Navigation/NavigationLinks.vue';
import { useDark, useToggle } from "@vueuse/core";

import SearchIcon from '~/images/icons/search.svg';
import OpenNavSVG from '~/images/icons/3lines.svg';
import CloseNavSVG from '~/images/icons/exit.svg';
import SunIcon from '~/images/icons/sun.svg';
import MoonIcon from '~/images/icons/moon.svg';

const isDark = useDark();
const toggleDark = useToggle(isDark);


const props = defineProps({
    showingNavigationDropdown: {
        type: Boolean,
        required: true
    },
    showingStudioLinks: {
        type: Boolean,
        required: true
    },

})


</script>

<template>
    <div class="h-16">
        <!--Nav is fixed so lets space things below-->

    </div>
    <nav class="bg-vidgaze-blue fixed z-40 top-0 w-full "  >
        <!-- Primary Navigation Menu -->
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex w-full">

                    <!-- Hamburger -->
                    <div class=" flex items-center  lg:hidden">
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
                    <div class="shrink-0 flex items-center -mr-5">
                        <Link :href="route('home')">
                            <img src="/images/logos/vidgaze/vidgaze_banner.png" alt="VidGaze Logo" class="h-10 w-auto">
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <NavigationLinks :showingStudioLinks="showingStudioLinks"/>
                    <!--Search bar-->
                    <div class="flex flex-col flex-grow  justify-center items-end sm:items-center sm:px-5">
                        <div class="flex flex-row  sm:gap-x-2 items-center text-zinc-500 p-2 px-3  mx-4 w-max sm:w-full max-w-md rounded-xl bg-zinc-900">
                            <SearchIcon class="w-5 h-5 flex-shrink-0"/>
                            <input type="text" class="w-0 sm:w-full   bg-transparent p-0 m-0 without-ring placeholder-zinc-500 text-white" placeholder="Search YouTube, Twitch and more...">
                        </div>
                    </div>

                    <!--log in-->
                    <div v-if="$page.props.auth.user == null" class="hidden lg:flex sm:items-center   flex-shrink-0">
                        <div class="flex gap-x-2">
                            <Link :href="route('login')" class="text-sm text-zinc-300 hover:text-zinc-400">Log In</Link>
                            <p class="text-sm text-zinc-300">/</p>
                            <Link :href="route('register')" class="text-sm text-zinc-300 hover:text-zinc-400">Sign Up</Link>
                        </div>
                    </div>

                </div>

                <div v-if="$page.props.auth.user != null"  class=" flex  items-center   flex-shrink-0">
                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <Dropdown align="right" width="48" distance="1">
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
                                <DropdownLink :href="route('profile.edit')"> Manage Your Account </DropdownLink>
                                <DropdownLink :href="route('profile.edit')"> Your Channel </DropdownLink>
                                <DropdownLink :href="route('studio.dashboard')"> VidGaze Studio </DropdownLink>
                                <DropdownLink :href="route('profile.edit')"> VidCoins </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>


            </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
            :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
            class=" "
        >
            <div class="pt-2 pb-3 space-y-1">
                <ResponsiveNavLink :href="route('videos')" :active="route().current('videos')">
                    Videos
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('streams')" :active="route().current('streams')">
                    Live
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('shorts')" :active="route().current('shorts')">
                    Shorts
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('music')" :active="route().current('music')">
                    Music
                </ResponsiveNavLink>
                <ResponsiveNavLink :href="route('podcasts')" :active="route().current('podcasts')">
                    Podcasts
                </ResponsiveNavLink>
            </div>

            <!--                dark/light mode-->
            <div class="w-full h-full flex cursor-pointer">

                <div v-if="isDark" @click="toggleDark()" class="w-5 aspect-square text-white   ">
                    <SunIcon class="w-5 h-5"/>
                </div>
                <div v-if="!isDark" @click="toggleDark()"  class="w-5 aspect-square  text-white   ">
                    <MoonIcon class="w-5 h-5"/>
                </div>

            </div>


            <!-- Responsive Settings Options -->
            <div  v-if="$page.props.auth.user != null"  class="pt-4 pb-1 border-t border-zinc-200 dark:border-zinc-600">



                <div class="mt-3 space-y-1">
<!--                    <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>-->
                    <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                        Log Out
                    </ResponsiveNavLink>
                </div>
            </div>
        </div>
    </nav>

</template>

