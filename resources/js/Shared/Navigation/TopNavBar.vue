<script setup>
import ProfileDropdown from "@/Components/Dropdown/ProfileDropdown.vue";
import UploadDropdown from "@/Components/Dropdown/UploadDropdown.vue";
import NotificationsDropdown from "@/Components/Dropdown/NotificationsDropdown.vue";
import TopNavigationLinks from '@/Shared/Navigation/Partials/TopNavigationLinks.vue';
import OpenNavSVG from '~/images/icons/3lines.svg';
import CloseNavSVG from '~/images/icons/exit.svg';

import {Link} from "@inertiajs/vue3";
import Searchbar from "@/Shared/Navigation/Partials/Searchbar.vue";

//name of the component
const name = 'TopNavBar';

//accept props
const props = defineProps({
    expandedSearchBar: {
        type: Boolean,
        required: true,
        default: false
    },
    showingNavigationDropdown: {
        type: Boolean,
        required: true,
        default: false
    },
    showingStudioLinks: {
        type: Boolean,
        required: false
    },
    expandedSearchResults: {
        type: Boolean,
        required: false,
        default: false
    },
});



</script>

<template>

    <!--Top nav-->
    <div class="bg-vidgaze-blue-nav pointer-events-auto">
        <div class="max-w- screen-2xl mx-auto px-4 sm:px-6 lg:px-8  flex flex-col">
            <div class="flex justify-between h-16 ">
                <div class="flex w-full">
                    <!-- Hamburger, hide whenever search icon is clicked in mobile mode -->
                    <div class=" flex items-center  "
                         :class="{'hidden sm:flex': expandedSearchBar,  '': !expandedSearchBar }">
                        <button
                            @click="$emit('toggleSidenav')"
                            :class="{
                                'rotate-180 ': showingNavigationDropdown,
                            }"
                            class=" transition duration-900 ease-in-out inline-flex items-center justify-center p-2 rounded-md text-zinc-400 dark:text-zinc-500 hover:text-zinc-500 dark:hover:text-zinc-400  focus:outline-none  focus:text-zinc-400 transition duration-150 ease-in-out"
                        >
                            <OpenNavSVG class="h-6 w-6 fill-white" />
                            <!--<CloseNavSVG class="h-6 w-6 fill-white" :class="{-->
                            <!--                hidden: !showingNavigationDropdown,-->
                            <!--                'inline-flex': showingNavigationDropdown,-->
                            <!--            }"/>-->
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

                    <Searchbar @toggleExpandedSearchBarOn="$emit('toggleExpandedSearchBarOn')"
                                 @toggleExpandedSearchBarOff="$emit('toggleExpandedSearchBarOff')"
                               @toggleExpandedSearchResultsOn="$emit('toggleExpandedSearchResultsOn')"
                               @toggleExpandedSearchResultsOff="$emit('toggleExpandedSearchResultsOff')"
                               v-if="!showingStudioLinks" :expandedSearchBar="expandedSearchBar" :expandedSearchResults="expandedSearchResults" />

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

                <div v-if="$page.props.auth.user != null" class=" flex flex-row space-x-5 items-center   flex-shrink-0"
                     :class="{'hidden sm:flex': expandedSearchBar,  '': !expandedSearchBar }"
                >
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

</template>



