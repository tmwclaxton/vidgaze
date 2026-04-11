<script setup>

import ExpandableNavigationLinks from "@/Shared/Navigation/Partials/SideNavigationLinks.vue";
import SidebarNavLink from '@/Shared/Navigation/SidebarNavLink.vue';
import SidebarFooterLink from '@/Shared/Navigation/SidebarFooterLink.vue';
import SunIcon from '~/images/icons/sun.svg';
import MoonIcon from '~/images/icons/moon.svg';
import LogoutIcon from '~/images/icons/logout.svg';
import StudioIcon from '~/images/icons/light.svg';
import SettingsIcon from '~/images/icons/settings.svg';
import ProfileIcon from '~/images/icons/profile.svg';
import {useDark, useToggle} from "@vueuse/core";
import {ref, watch, nextTick} from "vue";
import {usePage} from "@inertiajs/vue3";
import {useNavStore} from "@/Stores/NavStore";
import {useAuthStore} from "@/Stores/AuthStore";
import { openRegisterModal } from '@/utils/authGate';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
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
                            <SidebarNavLink :href="route('profile.edit')" label="Manage Your Account">
                                <template #icon="{ svgClass }">
                                    <SettingsIcon :class="svgClass" />
                                </template>
                            </SidebarNavLink>
                            <SidebarNavLink :href="route('profile.edit')" label="Your Channel">
                                <template #icon="{ svgClass }">
                                    <ProfileIcon :class="svgClass" />
                                </template>
                            </SidebarNavLink>
                            <SidebarNavLink :href="route('studio.dashboard')" label="VidGaze Studio">
                                <template #icon="{ svgClass }">
                                    <StudioIcon :class="svgClass" />
                                </template>
                            </SidebarNavLink>
                        </div>


                        <!-- Responsive Settings Options -->
                        <div v-if="authStore.user != null" class="lg:hidden" >


                            <div class="mt-1 space-y-1 hidden">

                                <SidebarNavLink label="Log Out" @click="authStore.logout()" method="post" as="button">
                                    <template #icon="{ svgClass }">
                                        <LogoutIcon :class="svgClass" />
                                    </template>
                                </SidebarNavLink>
                            </div>
                        </div>
                        <div v-else class="sm:hidden">
                            <div class="mt-1 space-y-1">
                                <!-- <ResponsiveNavLink :href="route('login')"> Log In </ResponsiveNavLink>-->
                                <SidebarNavLink href="#" label="Sign Up" @click.prevent="openRegisterModal()">
                                    <template #icon="{ svgClass }">
                                        <ProfileIcon :class="svgClass" />
                                    </template>
                                </SidebarNavLink>
                            </div>
                        </div>
                    </div>


                    <!--Everything below here should always be on the screen-->


                    <p
                        v-if="navStore.getNavigationDropdown()"
                        class="mb-1.5 px-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-cyan-400/90 sm:px-0.5"
                    >
                        Appearance
                    </p>
                    <!--                dark/light mode-->
                    <div class="group/appearance cursor-pointer space-y-1" @click="toggleDark()">
                        <span v-if="!isDark">
                            <SidebarNavLink :span="true" label="Light">
                                <template #icon="{ svgClass }">
                                    <SunIcon :class="svgClass" />
                                </template>
                            </SidebarNavLink>
                        </span>
                        <span v-else>
                            <SidebarNavLink :span="true" label="Dark">
                                <template #icon="{ svgClass }">
                                    <MoonIcon :class="svgClass" />
                                </template>
                            </SidebarNavLink>
                        </span>
                    </div>

                    <SidebarNavLink
                        :span="true"
                        label="Toggle Shorts"
                        :label-class="[
                            navStore.getNavigationDropdown() ? 'whitespace-nowrap' : 'max-w-full text-center leading-snug',
                        ]"
                        @click="toggleShorts()"
                    >
                        <template #icon="{ faClass }">
                            <font-awesome-icon
                                v-if="useAuthStore().areShortsEnabled()"
                                :icon="['fas', 'toggle-on']"
                                :class="faClass"
                            />
                            <font-awesome-icon
                                v-else
                                :icon="['fas', 'toggle-off']"
                                :class="faClass"
                            />
                        </template>
                    </SidebarNavLink>
                </div>


                <div id="bottom" v-if="navStore.getBottomNavLinks()" class="mt-2 border-t border-white/[0.06] pt-2 pb-0.5">

                    <!--add about page-->
                    <div class="grid grid-cols-1 gap-1 text-center" :class="{ 'grid-cols-4': navStore.getNavigationDropdown() }">
                        <SidebarFooterLink :href="route('about')" :active="route().current('about')" label="About" />
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



