<script setup>
import Dropdown from '@/Components/Dropdown/Dropdown.vue';
import DropdownLink from '@/Components/Dropdown/DropdownLink.vue';

import ResponsiveNavLink from '@/Components/Links/ResponsiveNavLink.vue';
import {Link} from '@inertiajs/vue3';
import {useDark, useToggle} from "@vueuse/core";


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
// import { AiCrossref } from "oh-vue-icons/icons";


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
    <nav class=" fixed z-40 top-0 w-full " >
        <!-- Primary Navigation Menu -->
        <div class=" max-w-screen-2xl  bg-vidgaze-blue mx-auto px-4 sm:px-6 lg:px-8  flex flex-col trans" :class="{
                                            'h-screen lg:h-max': showingNavigationDropdown,
                                            '': !showingNavigationDropdown,
                                        }">
            <div class="flex justify-between h-16 ">
                <div class="flex w-full">

                    <!-- Hamburger -->
                    <div class=" flex items-center  ">
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
                    <div class="shrink-0 flex items-center  md:mr-5">
                        <Link :href="route('home')">
                            <img src="/images/logos/vidgaze/vidgaze_banner.png" alt="VidGaze Logo" class="h-10 w-auto">
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <TopNavigationLinks :showingStudioLinks="showingStudioLinks"/>
                    <!--Search bar-->
                    <div class="flex flex-col flex-grow  justify-center items-end sm:items-center sm:px-5">
                        <div
                            class="flex flex-row  sm:gap-x-2 items-center text-zinc-500 p-2 px-3  mx-2 w-max sm:w-full max-w-md rounded-xl bg-zinc-900">
                            <SearchIcon class="w-5 h-5 flex-shrink-0"/>
                            <input type="text"
                                   class="w-0 sm:w-full   bg-transparent p-0 m-0 without-ring placeholder-zinc-500 text-white"
                                   placeholder="Search YouTube, Twitch and more...">
                        </div>
                    </div>

                    <!--log in-->
                    <div v-if="$page.props.auth.user == null" class="hidden lg:flex sm:items-center   flex-shrink-0">
                        <div class="flex gap-x-2">
                            <Link :href="route('login')" class="text-sm text-zinc-300 hover:text-zinc-400">Log In</Link>
                            <p class="text-sm text-zinc-300">/</p>
                            <Link :href="route('register')" class="text-sm text-zinc-300 hover:text-zinc-400">Sign Up
                            </Link>
                        </div>
                    </div>

                </div>

                <div v-if="$page.props.auth.user != null" class=" flex  items-center   flex-shrink-0">
                    <!-- Profile Dropdown -->
                    <div class="relative hidden sm:flex">
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
                                <DropdownLink :href="route('profile.edit')"> Manage Your Account</DropdownLink>
                                <DropdownLink :href="route('profile.edit')"> Your Channel</DropdownLink>
                                <DropdownLink :href="route('studio.dashboard')"> VidGaze Studio</DropdownLink>
                                <DropdownLink :href="route('profile.edit')"> VidCoins</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>


            </div>



        <!-- Responsive Navigation Menu -->
        <div
            :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
            class=" pb-2 pt-2   flex flex-col justify-between flex-grow    "
        >

            <div id="top" class="">
                <ExpandableNavigationLinks :showingStudioLinks="showingStudioLinks"/>

                <div   class="border-t border-zinc-600 my-1  lg:hidden"></div>

                <div v-if="$page.props.auth.user != null" class="space-y-1 md:hidden">
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
                <div class="  lg:hidden  ">

                    <!-- Responsive Settings Options -->
                    <div v-if="$page.props.auth.user != null" >


                        <div class="mt-1 space-y-1">

                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                <div class="flex flex-row items-center gap-x-2">
                                    <LogoutIcon class="w-5 h-5"/>
                                    <span>Log Out</span>
                                </div>
                            </ResponsiveNavLink>
                        </div>
                    </div>
                    <div v-else class="">
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



                <!--                dark/light mode-->
                <div class="text-white cursor-pointer space-y-1" @click="toggleDark()">
                    <span v-if="!isDark">
                    <ResponsiveNavLink span="true">
                        <div  class="flex flex-row items-center gap-x-2">
                            <SunIcon class="w-5 h-5"/>
                            <span >Light Mode</span>
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
                            <font-awesome-icon :icon="['fas', 'heart']" />
                            <span>About</span>
                        </div>
                    </ResponsiveNavLink>
                </div>
                <!--add support page-->
                <div class="space-y-1">
                    <ResponsiveNavLink :href="route('about')">
                        <div class="flex flex-row items-center gap-x-2">
                            <font-awesome-icon :icon="['fass', 'phone']" />
                            <span>Support</span>
                        </div>
                    </ResponsiveNavLink>
                </div>
                <!--add policy page-->
                <div class="space-y-1">
                    <ResponsiveNavLink :href="route('about')">
                        <div class="flex flex-row items-center gap-x-2">
                            <font-awesome-icon :icon="['fass', 'scroll']" />
                            <span>Privacy Policy</span>
                        </div>
                    </ResponsiveNavLink>
                </div>
                <!--add terms page-->
                <div class="space-y-1">
                    <ResponsiveNavLink :href="route('about')">
                        <div class="flex flex-row items-center gap-x-2">
                            <font-awesome-icon :icon="['fass', 'asterisk']" />
                            <span>Terms of Service</span>
                        </div>
                    </ResponsiveNavLink>
                </div>

            </div>
        </div>








        </div>
    </nav>

</template>

