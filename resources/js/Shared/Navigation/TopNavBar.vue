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
                class="inline-flex items-center justify-center rounded-xl p-2 text-zinc-400 transition-all duration-200 ease-out hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30 active:scale-95"
                :class="{ 'bg-white/10 text-white': navStore.getNavigationDropdown() }"
            >
                <CloseNavSVG
                    v-if="navStore.getNavigationDropdown()"
                    class="h-6 w-6 shrink-0 fill-current"
                />
                <OpenNavSVG v-else class="h-6 w-6 shrink-0 fill-current" />
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
                                    class="group relative overflow-hidden border border-white/10 shadow-sm transition-[background,background-image,box-shadow,border-color,transform] duration-700 ease-in-out hover:!border-cyan-300/25 hover:!bg-gradient-to-r hover:!from-sky-800/95 hover:!via-cyan-800/90 hover:!to-teal-800/95 hover:!shadow-[0_0_40px_-12px_rgba(34,211,238,0.18)] active:scale-[0.98]"
                                >
                                    <font-awesome-icon
                                        :icon="['fas', 'right-to-bracket']"
                                        class="h-4 w-auto transition-transform duration-700 ease-in-out group-hover:scale-105"
                                    />
                                    <p>Log In</p>
                                </TopNavButton>

                            </div>
                            <div @click="redirect('register')" >

                                <TopNavButton
                                    class="group relative overflow-hidden border border-white/10 shadow-sm transition-[background,background-image,box-shadow,border-color,transform] duration-700 ease-in-out hover:!border-fuchsia-300/25 hover:!bg-gradient-to-r hover:!from-violet-800/95 hover:!via-fuchsia-800/90 hover:!to-pink-900/90 hover:!shadow-[0_0_40px_-12px_rgba(232,121,249,0.16)] active:scale-[0.98]"
                                >
                                    <font-awesome-icon
                                        :icon="['fas', 'user-plus']"
                                        class="h-4 w-auto transition-transform duration-700 ease-in-out group-hover:scale-105"
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



