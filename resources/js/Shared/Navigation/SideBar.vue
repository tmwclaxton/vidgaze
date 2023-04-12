<script setup>

import ResponsiveNavLink from '@/Components/Links/ResponsiveNavLink.vue';
import ExpandableNavigationLinks from "@/Shared/Navigation/Partials/SideNavigationLinks.vue";
import SunIcon from '~/images/icons/sun.svg';
import MoonIcon from '~/images/icons/moon.svg';
import LogoutIcon from '~/images/icons/logout.svg';
import StudioIcon from '~/images/icons/light.svg';
import SettingsIcon from '~/images/icons/settings.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useDark, useToggle} from "@vueuse/core";

const isDark = useDark();
const toggleDark = useToggle(isDark);


//name of the component
const name = 'SideBar';

//accept props
const props = defineProps({
    showingNavigationDropdown: {
        type: Boolean,
        required: true,
        default: false
    },
    showingStudioLinks: {
        type: Boolean,
        required: false
    },
})
</script>

<template>

    <div class="fixed top-0  flex flex-col h-screen w-screen sm:w-max" >
        <div class="h-16">
            <!--Nav is fixed so lets space things below-->

        </div>
    <!-- Responsive Navigation Menu -->
    <div class="  w-full    flex flex-row flex-grow top-0 ">
        <div
            :class="{ 'flex': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
            class="w-full sm:w-max mx-auto px-4 sm:px-6 lg:px-8 pb-2 pt-2  bg-vidgaze-blue-nav flex flex-col justify-between flex-grow pointer-events-auto   "
        >

            <div id="top" class="">
                <ExpandableNavigationLinks :showingStudioLinks="showingStudioLinks"/>

                <div class="border-t border-zinc-600 my-1 "></div>
                <div class="hidden">

                    <div v-if="$page.props.auth.user != null" class="space-y-1 sm:hidden ">
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
</template>



