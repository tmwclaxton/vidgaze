<script setup>
import ProfileDropdown from "@/Components/Dropdown/ProfileDropdown.vue";
import UploadDropdown from "@/Components/Dropdown/UploadDropdown.vue";
import NotificationsDropdown from "@/Components/Dropdown/NotificationsDropdown.vue";
import TopNavigationLinks from '@/Shared/Navigation/Partials/TopNavigationLinks.vue';
import OpenNavSVG from '~/images/icons/3lines.svg';
import CloseNavSVG from '~/images/icons/exit.svg';

import {Link} from "@inertiajs/vue3";
import Searchbar from "@/Shared/Navigation/Partials/Searchbar.vue";
import {useNavStore} from "@/Stores/NavStore";
const navStore = useNavStore();


//name of the component
const name = 'TopNavBar';





</script>

<template>

    <!--Top nav-->
    <div class="bg-vidgaze-blue-nav pointer-events-auto backdrop-blur-sm " >
        <div class="max-w- screen-2xl mx-auto pl-5 sm:pl-8 pr-4 sm:pr-6 lg:pr-8  flex flex-col">
            <div class="flex justify-between h-16 ">
                <div class="flex w-full">
                    <!-- Hamburger, hide whenever search icon is clicked in mobile mode -->
                    <div class=" flex items-center  "
                         :class="{'hidden sm:flex': navStore.getExpandedSearchBar(),  '': !navStore.getExpandedSearchBar() }">
                        <button
                            @click="navStore.toggleShowingNavigationDropdown()"
                            :class="{
                                'rotate-180 ': navStore.getNavigationDropdown(),
                            }"
                            class="bg-zi nc-900 dark:bg-vid gaze-blue rounded-full  transition duration-900 ease-in-out inline-flex items-center justify-center p-2  text-zinc-400 dark:text-zinc-500 hover:text-zinc-500 dark:hover:text-zinc-400  focus:outline-none  focus:text-zinc-400 transition duration-300 ease-in-out"
                        >
                            <OpenNavSVG class="h-6 w-6 fill-white" />
                            <!--<CloseNavSVG class="h-6 w-6 fill-white" :class="{-->
                            <!--                hidden: !showingNavigationDropdown,-->
                            <!--                'inline-flex': showingNavigationDropdown,-->
                            <!--            }"/>-->
                        </button>
                    </div>


                    <!-- Logo -->
                    <div class="shrink-0 flex items-center md:mr-5 sm:ml-6" :class="
                                {
                                    'hidden sm:flex': navStore.getExpandedSearchBar(),
                                    '': !navStore.getExpandedSearchBar(),
                                }">
                        <Link :href="route('home')">
                            <img src="/images/logos/vidgaze/vidgaze_banner.png" alt="VidGaze Logo"
                                 class="h-12 w-auto">
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <!--<TopNavigationLinks />-->

                    <Searchbar v-if="!navStore.getStudioLinks()"  />

                    <!--Buy Vidcoins button-->
                    <div v-if="!navStore.getStudioLinks()" class="hidden 2xl:flex sm:items-center mr-2 flex-shrink-0 group">
                        <Link :href="route('login')"  >
                            <button class="flex flex-row items-center justify-center
                            text-sm capitalize bg-zinc-900 hover:bg-zinc-800 text-white p-2 px-5 rounded-md font-bold flex flex-row space-x-3">
                                <img src="/images/vidcoins/coins/PileofCoins2.png" alt="VidCoins"
                                     class="h-4 w-auto group-hover:shake">
                                <p>Get VidCoins</p>
                            </button>
                        </Link>
                    </div>
                    <!--log in-->
                    <div v-if="$page.props.auth.user == null"
                         class="hidden sm:flex sm:items-center   flex-shrink-0">
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

                <div v-if="$page.props.auth.user != null" @click="$emit('toggleSidenavOff')"
                     class=" flex flex-row gap-x-5 items-center ml-4  flex-shrink-0  "
                     :class="{'hidden sm:flex': navStore.getExpandedSearchBar(),  '': !navStore.getExpandedSearchBar() }"
                >
                    <!--Upload Dropdown-->
                    <UploadDropdown v-if="!navStore.getStudioLinks()" />

                    <!--Notifications Dropdown-->
                    <NotificationsDropdown/>

                    <!-- Profile Dropdown -->
                    <ProfileDropdown/>
                </div>


            </div>
        </div>

    </div>

</template>



