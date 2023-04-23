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

    <div class="fixed top-0  flex flex-col h-screen pointer-events-none"
         >
        <div class="h-16">
            <!--Nav is fixed so lets space things below-->

        </div>
    <!-- Responsive Navigation Menu -->
        <div class="  flex flex-row flex-grow top-0 overflow-x-hidden w-full ml-0 "
             :class="{ 'opacity-0 pointer-events-none  ': !showingNavigationDropdown,
              'w-screen sm:w-56 opacity-100 pointer-events-auto': showingNavigationDropdown,
               'sm:opacity-100 sm:pointer-events-auto sm:flex sm:w-24' : !route().current('about') && !showingNavigationDropdown
        }">

            <div
                class="w-full mx-auto px-4 sm:px-2 lg:px-2 pb-2 pt-2  bg-vidgaze-blue-nav flex flex-col justify-between flex-grow    "
            >

                <div id="top" class="">
                    <ExpandableNavigationLinks :showingStudioLinks="showingStudioLinks" :showingNavigationDropdown="showingNavigationDropdown"/>

                    <div class="border-t border-zinc-600 my-1 "></div>
                    <div class="">

                        <div v-if="$page.props.auth.user != null" class="space-y-1 sm:hidden hidden">
                            <ResponsiveNavLink :href="route('profile.edit')"
                              :showingNavigationDropdown="showingNavigationDropdown"
                            >
                                <SettingsIcon class="w-5 h-5 flex-shrink-0"/>
                                <span>Manage Your Account</span>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('profile.edit')"
                              :showingNavigationDropdown="showingNavigationDropdown"
                            >
                                <ProfileIcon class="w-5 h-5 flex-shrink-0"/>
                                <span>Your Channel</span>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('studio.dashboard')">
                                <StudioIcon class="w-5 h-5 flex-shrink-0"/>
                                <span>VidGaze Studio</span>
                            </ResponsiveNavLink>
                        </div>


                        <!-- Responsive Settings Options -->
                        <div v-if="$page.props.auth.user != null" class="lg:hidden" >


                            <div class="mt-1 space-y-1 hidden">

                                <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                                      :showingNavigationDropdown="showingNavigationDropdown">
                                        <LogoutIcon class="w-5 h-5 flex-shrink-0"/>
                                        <span>Log Out</span>
                                </ResponsiveNavLink>
                            </div>
                        </div>
                        <div v-else class="sm:hidden">
                            <div class="mt-1 space-y-1">
                                <!-- <ResponsiveNavLink :href="route('login')"> Log In </ResponsiveNavLink>-->
                                <ResponsiveNavLink :href="route('register')"
                                      :showingNavigationDropdown="showingNavigationDropdown">
                                        <ProfileIcon class="w-5 h-5 flex-shrink-0"/>
                                        <span>Sign Up</span>
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>


                    <!--Everything below here should always be on the screen-->


                    <!--                dark/light mode-->
                    <div class="text-white cursor-pointer space-y-1" @click="toggleDark()">
                        <span v-if="!isDark">
                            <ResponsiveNavLink span="true"
                                  :showingNavigationDropdown="showingNavigationDropdown">
                                    <SunIcon class="w-5 h-5 flex-shrink-0"/>
                                    <span>Light</span>
                            </ResponsiveNavLink>
                        </span>
                        <span v-else>
                            <ResponsiveNavLink span="true"
                              :showingNavigationDropdown="showingNavigationDropdown">
                                    <MoonIcon class="w-5 h-5 flex-shrink-0"/>
                                    <span class="">Dark</span>
                            </ResponsiveNavLink>
                        </span>
                    </div>
                </div>


                <div id="bottom" class=" pb-1" :class="{ 'hid den ': !showingNavigationDropdown} ">

                    <!--add about page-->
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('about')"
                              :showingNavigationDropdown="showingNavigationDropdown">
                                <font-awesome-icon :icon="['fas', 'heart']" class="w-5 h-5 flex-shrink-0 transition delay-900"
                                                   :class="{ 'hidden ': !showingNavigationDropdown}" />
                                <p>About</p>
                        </ResponsiveNavLink>
                    </div>
                    <!--add support page-->
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('about')"
                          :showingNavigationDropdown="showingNavigationDropdown">
                                <font-awesome-icon :icon="['fass', 'phone']" class="w-5 h-5 flex-shrink-0"
                                                   :class="{ 'hidden ': !showingNavigationDropdown}" />
                                <span>Support</span>
                        </ResponsiveNavLink>
                    </div>
                    <!--add policy page-->
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('about')"
                          :showingNavigationDropdown="showingNavigationDropdown">
                                <font-awesome-icon :icon="['fass', 'scroll']" class="w-5 h-5 flex-shrink-0"
                                                   :class="{ 'hidden ': !showingNavigationDropdown}" />
                                <span>Privacy</span>
                        </ResponsiveNavLink>
                    </div>
                    <!--add terms page-->
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('about')"
                              :showingNavigationDropdown="showingNavigationDropdown">
                                <font-awesome-icon :icon="['fass', 'asterisk']" class="w-5 h-5 flex-shrink-0"
                                                   :class="{ 'hidden ': !showingNavigationDropdown}" />
                                <span>Terms</span>
                        </ResponsiveNavLink>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>



