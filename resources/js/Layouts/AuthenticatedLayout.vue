<script setup>
import {computed, onMounted, ref} from 'vue';

import Nav from '@/Shared/Navigation/Nav.vue';
import ToastList from "@/Components/Toast/ToastList.vue";
import {useNavStore} from "@/Stores/NavStore";
import VideoStreamModal from "@/Components/Modals/ContentModal.vue";
import PlaylistModal from "@/Components/Modals/PlaylistModal.vue";
import VideoStreamMiniPlayer from "@/Components/Modals/MiniPlayers/VideoStreamMiniPlayer.vue";
import PodcastMiniPlayer from "@/Components/Podcasts/PodcastMiniPlayer.vue";
import ShareModel from "@/Components/Modals/ShareModel.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import {usePage} from "@inertiajs/vue3";
import {usePlayerStore} from "@/Stores/PlayerStore";
import {useAuthStore} from "@/Stores/AuthStore";
import PlaylistPageModal from "@/Components/Modals/PlaylistPageModal.vue";
import BottomNavBar from "@/Shared/Navigation/BottomNavBar.vue";
import PinModal from "@/Components/Modals/PinModal.vue";
import AuthModal from "@/Components/Modals/AuthModal.vue";
import { useAuthModalStore } from "@/Stores/AuthModalStore";
import { router } from "@inertiajs/vue3";
const authStore = useAuthStore();
const authModalStore = useAuthModalStore();
const navStore = useNavStore();
const inertiaPage = usePage();
/** Forces a full page subtree remount on each visit so DOM from Watch (e.g. players) cannot patch against Home. */
const layoutPageKey = computed(() => `${inertiaPage.url}::${inertiaPage.component}`);
const name = 'AuthenticatedLayout';
let showingNavigationDropdown = ref(false);



onMounted(() => {

    usePlayerStore().loadScripts();
    // use authStore to get user if token is set in local storage
            if (authStore.user === null && localStorage.getItem('token') !== null) {
        authStore.getUser().then(() => {
            // if user is null & page prop auth_routes is true, redirect to home
            if (authStore.user === null && inertiaPage.props.auth_route) {
                window.location.href = route('home');
            }
        });
    }

    const am = inertiaPage.props.auth_modal;
    if (am?.should_open) {
        const url = am.intended_url;
        authModalStore.open('login', url ? () => router.visit(url) : null);
    }

});

</script>

<template>



    <div >

        <!-- this is where the toast message popup is added -->
        <ToastList :flash="$page.props.flash"/>

        <div class="flex flex-col  relative min-h-screen">

            <Nav v-if="inertiaPage.props.layoutDisplay !== 'auth'"/>

            <!-- Page Content -->
            <main class="h-full flex flex-row flex-grow    " >

                <div v-if="inertiaPage.props.layoutDisplay !== 'auth' && inertiaPage.props.layoutDisplay !== 'wide'"
                     class="pointer-events-none flex-shrink-0 opacity-0 transition ease-in-out"  :class="{'sm:w-64  ': navStore.getNavigationDropdown(), 'sm:w-nav-rail': !navStore.getNavigationDropdown()}">

                </div>
                <div class="relative flex-shrink transition duration-700 ease-in-out w-full min-w-0">

                    <div :key="layoutPageKey" class="w-full">
                        <slot/>
                    </div>
                    <!--Modals we want centered with side bar-->
                    <PlaylistModal/>
                    <PinModal/>
                    <ShareModel/>
                    <ConfirmModal />
                    <AuthModal />
                </div>

                <!--Modals that don't matter how they are -->
                <VideoStreamMiniPlayer/>
                <PodcastMiniPlayer/>
                <VideoStreamModal/>

                <!--<CookieConsent/>-->

            </main>

            <BottomNavBar />

        </div>
    </div>
</template>
