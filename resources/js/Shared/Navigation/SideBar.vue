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
import {ref, watch, nextTick} from "vue";
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
const drawerPanelRef = ref(null);

const FOCUSABLE_SELECTOR =
    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

function focusableInDrawer() {
    const root = drawerPanelRef.value;
    if (!root) {
        return [];
    }
    return Array.from(root.querySelectorAll(FOCUSABLE_SELECTOR)).filter((el) => {
        if (el.hasAttribute('disabled') || el.getAttribute('aria-hidden') === 'true') {
            return false;
        }
        const s = window.getComputedStyle(el);
        return s.visibility !== 'hidden' && s.display !== 'none';
    });
}

function focusFirstInDrawer() {
    const list = focusableInDrawer();
    if (list.length) {
        list[0].focus();
    } else {
        drawerPanelRef.value?.focus();
    }
}

function focusMenuButton() {
    document.querySelector('button[aria-controls="navigation-drawer"]')?.focus();
}

function onDrawerKeydown(e) {
    if (e.key !== 'Tab' || !navStore.showingNavigationDropdown) {
        return;
    }
    const list = focusableInDrawer();
    if (!list.length) {
        return;
    }
    const first = list[0];
    const last = list[list.length - 1];
    if (e.shiftKey) {
        if (document.activeElement === first) {
            e.preventDefault();
            last.focus();
        }
    } else if (document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
}

watch(
    () => navStore.showingNavigationDropdown,
    async (open, wasOpen) => {
        await nextTick();
        if (open) {
            requestAnimationFrame(() => focusFirstInDrawer());
        } else if (wasOpen) {
            focusMenuButton();
        }
    }
);

const toggleShorts = () => {
    useAuthStore().toggleShorts();
    keyRefresh.value = Math.random();
};

const labelGradient = (from, to) =>
    `font-medium bg-gradient-to-r ${from} ${to} bg-clip-text text-transparent transition-all duration-200 group-hover:brightness-110`;


// const isScreenLess = computed(() => {
//     return (window.innerWidth < 1200 && useAuthStore().user != null && !props.showingNavigationDropdown);
// });
// :class="{ 'hidden': authStore.user != null && !showingNavigationDropdown} "
</script>

<template>

    <div class="pointer-events-none fixed top-0 z-40 flex h-full flex-col overflow-y-auto overscroll-y-contain">
        <!-- Match topbar height exactly (no negative margins on topbar) so there is no hairline gap -->
        <div class="h-16 shrink-0" aria-hidden="true"></div>
        <!-- Responsive Navigation Menu -->
        <div
            id="navigation-drawer"
            ref="drawerPanelRef"
            tabindex="-1"
            class="flex min-h-0 flex-grow flex-row overflow-x-hidden overflow-y-auto bg-vidgaze-blue-nav backdrop-blur-md transition-[opacity,width] duration-200 ease-out motion-reduce:transition-none sm:border-r sm:border-white/[0.06] sm:shadow-[4px_0_24px_-8px_rgba(0,0,0,0.5)]"
            role="navigation"
            aria-label="Main menu"
            @keydown="onDrawerKeydown"
            :class="{
                'pointer-events-none w-0 min-w-0 opacity-0 sm:w-0': !navStore.getNavigationDropdown() && usePage().props.layoutDisplay === 'wide',
                'pointer-events-none opacity-0': !navStore.getNavigationDropdown() && usePage().props.layoutDisplay !== 'wide',
                'pointer-events-auto w-screen opacity-100 sm:w-64': navStore.getNavigationDropdown(),
                'sm:pointer-events-auto sm:flex sm:w-nav-rail sm:opacity-100': usePage().props.layoutDisplay !== 'wide' && !navStore.getNavigationDropdown(),
            }"
        >

            <div
                class="mx-auto flex w-full flex-grow flex-col justify-between px-3 pb-3 pt-2 sm:px-2 sm:pb-2 sm:pt-2 lg:px-2.5"
            >

                <div class="min-h-0 flex-1">
                    <ExpandableNavigationLinks :key="keyRefresh" />

                    <div class="my-3 border-t border-white/[0.06] sm:my-2"></div>
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


                    <p
                        v-if="navStore.getNavigationDropdown()"
                        class="mb-1.5 bg-gradient-to-r from-cyan-400/90 to-fuchsia-400/90 bg-clip-text px-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-transparent sm:px-0.5"
                    >
                        Appearance
                    </p>
                    <!--                dark/light mode-->
                    <div class="group/appearance cursor-pointer space-y-1" @click="toggleDark()">
                        <span v-if="!isDark">
                            <ResponsiveNavLink :span="true">
                                <SunIcon
                                    class="h-5 w-5 shrink-0 fill-current text-amber-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(251,191,36,0.55)] group-hover/appearance:scale-110 group-hover/appearance:drop-shadow-[0_0_14px_rgba(251,191,36,0.85)]"
                                />
                                <span :class="labelGradient('from-amber-300', 'to-yellow-400')">Light</span>
                            </ResponsiveNavLink>
                        </span>
                        <span v-else>
                            <ResponsiveNavLink :span="true">
                                <MoonIcon
                                    class="h-5 w-5 shrink-0 fill-current text-indigo-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(129,140,248,0.55)] group-hover/appearance:scale-110 group-hover/appearance:drop-shadow-[0_0_14px_rgba(167,139,250,0.85)]"
                                />
                                <span :class="labelGradient('from-indigo-300', 'to-violet-400')">Dark</span>
                            </ResponsiveNavLink>
                        </span>
                    </div>

                    <ResponsiveNavLink :span="true" @click="toggleShorts()">
                        <font-awesome-icon
                            v-if="useAuthStore().areShortsEnabled()"
                            :icon="['fas', 'toggle-on']"
                            class="h-5 w-5 shrink-0 leading-none text-emerald-400 transition-all duration-200 drop-shadow-[0_0_10px_rgba(52,211,153,0.55)] group-hover:scale-110 group-hover:drop-shadow-[0_0_14px_rgba(52,211,153,0.85)]"
                        />
                        <font-awesome-icon
                            v-if="!useAuthStore().areShortsEnabled()"
                            :icon="['fas', 'toggle-off']"
                            class="h-5 w-5 shrink-0 leading-none text-orange-400/90 transition-all duration-200 drop-shadow-[0_0_8px_rgba(251,146,60,0.45)] group-hover:scale-110 group-hover:text-orange-300 group-hover:drop-shadow-[0_0_12px_rgba(251,146,60,0.65)]"
                        />
                        <span
                            class="min-w-0"
                            :class="[
                                navStore.getNavigationDropdown() ? 'whitespace-nowrap' : 'max-w-full text-center leading-snug',
                                labelGradient('from-amber-300', 'to-orange-500'),
                            ]"
                        >Toggle Shorts</span>
                    </ResponsiveNavLink>
                </div>


                <div id="bottom" v-if="navStore.getBottomNavLinks()" class="mt-2 border-t border-white/[0.06] pt-2 pb-0.5">

                    <!--add about page-->
                    <div class="grid grid-cols-1 gap-1 text-center" :class="{ 'grid-cols-4': navStore.getNavigationDropdown() }">
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



