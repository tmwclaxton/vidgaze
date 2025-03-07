<script setup>

import ResponsiveNavLink from '@/Components/Links/ResponsiveNavLink.vue';
import ExpandableNavigationLinks from "@/Shared/Navigation/Partials/SideNavigationLinks.vue";
import SunIcon from '~/images/icons/sun.svg';
import MoonIcon from '~/images/icons/moon.svg';
import LogoutIcon from '~/images/icons/logout.svg';
import StudioIcon from '~/images/icons/light.svg';
import SettingsIcon from '~/images/icons/settings.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import {useDark, useToggle} from "@vueuse/core";
import ResponsiveNavBottomLink from "@/Components/Links/ResponsiveNavBottomLink.vue";
import {computed, ref} from "vue";
import {usePage} from "@inertiajs/vue3";
import {useNavStore} from "@/Stores/NavStore";
import {useAuthStore} from "@/Stores/AuthStore";
const navStore = useNavStore();
const authStore = useAuthStore();

const isDark = useDark();
const toggleDark = useToggle(isDark);


//name of the component
const name = 'SideBar';

const keyRefresh = ref(0);
const toggleShorts = () => {
    useAuthStore().toggleShorts();
    keyRefresh.value = Math.random();
};


// const isScreenLess = computed(() => {
//     return (window.innerWidth < 1200 && useAuthStore().user != null && !props.showingNavigationDropdown);
// });
// :class="{ 'hidden': authStore.user != null && !showingNavigationDropdown} "
</script>

<template>

    <div class="fixed top-0  flex flex-col  overflow-y-auto h-full pointer-events-none"
         >
        <div class="h-16 flex-shrink-0">
            <!--Nav is fixed so lets space things below-->

        </div>
    <!-- Responsive Navigation Menu -->
        <div class="  flex flex-row flex-grow top-0 overflow-x-hidden w-full ml-0  bg-vidgaze-blue-nav "
             :class="{ 'opacity-0 pointer-events-none  ': !navStore.getNavigationDropdown(),
              'w-screen sm:w-64 opacity-100 pointer-events-auto': navStore.getNavigationDropdown(),
               'sm:opacity-100 sm:pointer-events-auto sm:flex sm:w-24' :  usePage().props.layoutDisplay !== 'wide' && !navStore.getNavigationDropdown()
        }">

            <div
                class="w-full mx-auto px-4 sm:px-2 lg:px-2 pb-2 pt-2 flex flex-col justify-between flex-grow    "
            >

                <div class="">
                    <ExpandableNavigationLinks :key="keyRefresh" />

                    <div class="border-t border-zinc-600 my-1 "></div>
                    <div class="">

                        <div v-if="authStore.user != null" class="space-y-1 sm:hidden hidden">
                            <ResponsiveNavLink :href="route('profile.edit')"
                            >
                                <SettingsIcon class="w-5 h-5 flex-shrink-0"/>
                                <span>Manage Your Account</span>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('profile.edit')"                            >
                                <ProfileIcon class="w-5 h-5 flex-shrink-0"/>
                                <span>Your Channel</span>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('studio.dashboard')">
                                <StudioIcon class="w-5 h-5 flex-shrink-0"/>
                                <span>VidGaze Studio</span>
                            </ResponsiveNavLink>
                        </div>


                        <!-- Responsive Settings Options -->
                        <div v-if="authStore.user != null" class="lg:hidden" >


                            <div class="mt-1 space-y-1 hidden">

                                <ResponsiveNavLink  @click="authStore.logout()" method="post" as="button">
                                        <LogoutIcon class="w-5 h-5 flex-shrink-0"/>
                                        <span>Log Out</span>
                                </ResponsiveNavLink>
                            </div>
                        </div>
                        <div v-else class="sm:hidden">
                            <div class="mt-1 space-y-1">
                                <!-- <ResponsiveNavLink :href="route('login')"> Log In </ResponsiveNavLink>-->
                                <ResponsiveNavLink :href="route('register')">
                                        <ProfileIcon class="w-5 h-5 flex-shrink-0"/>
                                        <span>Sign Up</span>
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>


                    <!--Everything below here should always be on the screen-->


                    <!--                dark/light mode-->
                    <div class="text-white cursor-pointer space-y-1 " @click="toggleDark()">
                        <span v-if="!isDark">
                            <ResponsiveNavLink :span="true">
                                    <SunIcon class="w-5 h-5 flex-shrink-0"/>
                                    <span>Light</span>
                            </ResponsiveNavLink>
                        </span>
                        <span v-else>
                            <ResponsiveNavLink :span="true">
                                    <MoonIcon class="w-5 h-5 flex-shrink-0"/>
                                    <span class="">Dark</span>
                            </ResponsiveNavLink>
                        </span>
                    </div>

                    <ResponsiveNavLink :span="true" @click="toggleShorts()">
                        <font-awesome-icon v-if="useAuthStore().areShortsEnabled()"
                                           :icon="['fas', 'toggle-on']" class="w-5 h-5 flex-shrink-0"/>
                        <font-awesome-icon v-if="!useAuthStore().areShortsEnabled()"
                                           :icon="['fas', 'toggle-off']" class="w-5 h-5 flex-shrink-0"/>
                        <span>Toggle Shorts</span>
                    </ResponsiveNavLink>
                </div>


                <div id="bottom" class=" pb-1" v-if="navStore.getBottomNavLinks()">

                    <!--add about page-->
                    <div class="gap-1 grid grid-cols-1 text-center " :class="{ ' grid-cols-4': navStore.getNavigationDropdown()} ">
                        <ResponsiveNavBottomLink :href="route('about')" :active="route().current('about')">
                                <!--<font-awesome-icon :icon="['fas', 'heart']" class="w-4 h-4 flex-shrink-0  "-->
                                <!--                   :class="{ 'hidden ': !showingNavigationDropdown}" />-->
                            <span class="w-full">About</span>
                        </ResponsiveNavBottomLink>
                    <!--add support page-->
<!--                        <ResponsiveNavBottomLink :href="route('about') + '#support'"  >-->
<!--                                &lt;!&ndash;<font-awesome-icon :icon="['fass', 'phone']" class="w-4 h-4 flex-shrink-0"&ndash;&gt;-->
<!--                                &lt;!&ndash;                   :class="{ 'hidden ': !showingNavigationDropdown}" />&ndash;&gt;-->
<!--                                <span class="w-full">Support</span>-->
<!--                        </ResponsiveNavBottomLink>-->
<!--                    &lt;!&ndash;add policy page&ndash;&gt;-->
<!--                        <ResponsiveNavBottomLink :href="route('privacy')" :active="route().current('privacy')">-->
<!--                                &lt;!&ndash;<font-awesome-icon :icon="['fass', 'scroll']" class="w-4 h-4 flex-shrink-0"&ndash;&gt;-->
<!--                                &lt;!&ndash;                   :class="{ 'hidden ': !showingNavigationDropdown}" />&ndash;&gt;-->
<!--                            <span class="w-full">Privacy</span>-->
<!--                        </ResponsiveNavBottomLink>-->
<!--                    &lt;!&ndash;add terms page&ndash;&gt;-->
<!--                        <ResponsiveNavBottomLink :href="route('terms')" :active="route().current('terms')">-->
<!--                                &lt;!&ndash;<font-awesome-icon :icon="['fass', 'asterisk']" class="w-4 h-4 flex-shrink-0"&ndash;&gt;-->
<!--                                &lt;!&ndash;                   :class="{ 'hidden ': !showingNavigationDropdown}" />&ndash;&gt;-->
<!--                                <span class="w-full">Terms</span>-->
<!--                        </ResponsiveNavBottomLink>-->
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>



