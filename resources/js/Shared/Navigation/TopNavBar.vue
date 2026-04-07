<script setup>
import ProfileDropdown from "@/Components/Dropdown/ProfileDropdown.vue";
import UploadDropdown from "@/Components/Dropdown/UploadDropdown.vue";
import NotificationsDropdown from "@/Components/Dropdown/NotificationsDropdown.vue";
import OpenNavSVG from '~/images/icons/3lines.svg';
import CloseNavSVG from '~/images/icons/exit.svg';

import {Link, router} from "@inertiajs/vue3";
import Searchbar from "@/Shared/Navigation/Partials/Searchbar.vue";
import TopNavButton from "@/Components/Buttons/QuaternaryButton.vue";
import {useNavStore} from "@/Stores/NavStore";
const navStore = useNavStore();
import {useAuthStore} from "@/Stores/AuthStore";
const authStore = useAuthStore();

//name of the component
const name = 'TopNavBar';


function redirect(which) {
    localStorage.removeItem('intended');
    // if not on about page set intended to current page if route current not about
    if (!route().current("landing")) {  //
        localStorage.setItem('intended', window.location.href);
    }
    if (which === "login") {
        router.visit(route('login'));
    } else if (which === "register") {
        router.visit(route('register'));
    }
}


</script>

<template>

    <!--Top nav-->
    <div
        id="topNavBar"
        class="z-50 flex h-16 w-full items-stretch bg-vidgaze-blue-nav backdrop-blur-md backdrop-saturate-150 pointer-events-auto"
        :class="
            navStore.getNavigationDropdown()
                ? 'shadow-[0_8px_30px_-12px_rgba(0,0,0,0.55)]'
                : 'shadow-[0_1px_0_0_rgba(255,255,255,0.06),0_8px_30px_-12px_rgba(0,0,0,0.55)]'
        "
    >
        <!-- Same width + horizontal padding as sidebar rail so the menu icon lines up with nav icons -->
        <div
            class="hidden h-full w-nav-rail shrink-0 items-center justify-center px-2 sm:flex"
            :class="[
                { '!hidden': navStore.getExpandedSearchBar() },
                navStore.getNavigationDropdown() ? '' : 'border-r border-white/[0.06]',
            ]"
        >
            <button
                type="button"
                :aria-label="navStore.getNavigationDropdown() ? 'Close navigation menu' : 'Open navigation menu'"
                aria-controls="navigation-drawer"
                :aria-expanded="navStore.getNavigationDropdown()"
                @click="navStore.toggleShowingNavigationDropdown()"
                class="group inline-flex items-center justify-center rounded-xl border border-transparent p-2 transition-all duration-200 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400/40 active:scale-95"
                :class="
                    navStore.getNavigationDropdown()
                        ? 'bg-fuchsia-500/15 text-fuchsia-300 shadow-[0_0_22px_-4px_rgba(232,121,249,0.55)] hover:bg-fuchsia-500/25 hover:text-fuchsia-200 hover:shadow-[0_0_28px_-2px_rgba(244,114,182,0.45)] hover:border-fuchsia-400/35'
                        : 'text-cyan-400 shadow-[0_0_18px_-6px_rgba(34,211,238,0.45)] hover:border-cyan-400/35 hover:bg-cyan-500/15 hover:text-cyan-200 hover:shadow-[0_0_28px_-2px_rgba(34,211,238,0.55)]'
                "
            >
                <CloseNavSVG
                    v-if="navStore.getNavigationDropdown()"
                    class="h-6 w-6 shrink-0 fill-current transition-transform duration-200 group-hover:scale-110"
                />
                <OpenNavSVG
                    v-else
                    class="h-6 w-6 shrink-0 fill-current transition-transform duration-200 group-hover:scale-110"
                />
            </button>
        </div>

        <div class="flex min-h-16 min-w-0 flex-1 flex-col px-4 pl-5 sm:pl-4 sm:pr-6 lg:pr-8">
            <div class="flex h-16 min-h-16 items-center justify-between gap-2 sm:gap-4">
                <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
                    <!-- Logo -->
                    <div
                        class="flex shrink-0 flex-row items-center md:mr-5"
                        :class="{ 'hidden sm:flex': navStore.getExpandedSearchBar() }"
                    >
                        <Link v-if="!navStore.showingStudioLinks" :href="route('home')">
                            <img
                                src="/images/logos/vidgaze/vidgaze_banner.png"
                                alt="VidGaze Logo"
                                class="h-10 w-auto sm:h-11 drop-shadow-sm transition-opacity hover:opacity-90"
                            >
                        </Link>
                        <Link v-if="navStore.showingStudioLinks" :href="route('studio.dashboard')">
                            <img
                                src="/images/logos/vidgaze/studio.png"
                                alt="VidGaze Studio"
                                class="h-10 w-auto sm:h-11 drop-shadow-sm transition-opacity hover:opacity-90"
                            >
                        </Link>
                    </div>

                    <!-- Navigation Links -->
                    <!--<TopNavigationLinks />-->

                    <Searchbar v-if="!navStore.showingStudioLinks"  />

                    <!--Buy Vidcoins button-->
<!--                    <div v-if="!navStore.showingStudioLinks" class="hidden 2xl:flex sm:items-center mr-2 flex-shrink-0 group">-->
<!--                        <Link :href="route('marketplace')"  >-->

<!--                            <TopNavButton>-->
<!--                                <img src="/images/vidcoins/coins/PileofCoins2.png" alt="VidCoins"-->
<!--                                     class="h-4 w-auto group-hover:shake">-->
<!--                                <p>Get VidCoins</p>-->
<!--                            </TopNavButton>-->

<!--                        </Link>-->
<!--                    </div>-->
                    <!--log in-->
                    <div
                        v-if="authStore.user == null"
                        class="ml-auto hidden shrink-0 sm:flex sm:items-center"
                    >
                        <div class="flex flex-row-reverse gap-2">
                            <div @click="redirect('login')" >

                                <TopNavButton
                                    class="group relative overflow-hidden border border-cyan-400/40 !bg-gradient-to-r !from-sky-600 !via-cyan-600 !to-teal-600 !shadow-[0_0_22px_-6px_rgba(34,211,238,0.45)] transition-all duration-300 ease-out hover:!bg-gradient-to-r hover:!from-sky-500 hover:!via-cyan-500 hover:!to-teal-500 hover:!border-cyan-300/70 hover:!brightness-110 hover:!shadow-[0_0_36px_-2px_rgba(34,211,238,0.6)] hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]"
                                >
                                    <font-awesome-icon
                                        :icon="['fas', 'right-to-bracket']"
                                        class="h-4 w-auto transition-transform duration-300 group-hover:scale-110"
                                    />
                                    <p>Log In</p>
                                </TopNavButton>

                            </div>
                            <div @click="redirect('register')" >

                                <TopNavButton
                                    class="group relative overflow-hidden border border-fuchsia-400/40 !bg-gradient-to-r !from-violet-600 !via-fuchsia-600 !to-pink-600 !shadow-[0_0_22px_-6px_rgba(232,121,249,0.45)] transition-all duration-300 ease-out hover:!bg-gradient-to-r hover:!from-violet-500 hover:!via-fuchsia-500 hover:!to-pink-500 hover:!ring-2 hover:!ring-pink-400/55 hover:!shadow-[0_0_32px_4px_rgba(244,114,182,0.4)] hover:!saturate-150 hover:scale-[1.04] active:scale-[0.98]"
                                >
                                    <font-awesome-icon
                                        :icon="['fas', 'user-plus']"
                                        class="h-4 w-auto transition-transform duration-300 group-hover:rotate-12 group-hover:scale-110"
                                    />
                                    <p>Sign Up</p>
                                </TopNavButton>

                            </div>
                        </div>
                    </div>

                </div>

                <div
                    v-if="authStore.user != null"
                    class="ml-2 flex shrink-0 flex-row items-center gap-3 sm:ml-3 sm:gap-4"
                    :class="{ 'hidden sm:flex': navStore.getExpandedSearchBar() }"
                    @click="$emit('toggleSidenavOff')"
                >
                    <!--Upload Dropdown-->
                    <UploadDropdown v-if="!navStore.showingStudioLinks" />

                    <!--Notifications Dropdown-->
                    <NotificationsDropdown/>

                    <!-- Profile Dropdown -->
                    <ProfileDropdown/>
                </div>


            </div>
        </div>

    </div>

</template>



