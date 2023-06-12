<script setup>
import {computed, ref, watch} from 'vue';

import Nav from '@/Shared/Navigation/Nav.vue';
import ToastList from "@/Components/Toast/ToastList.vue";
import {useNavStore} from "@/Stores/NavStore";
import VideoStreamModal from "@/Components/Modals/ContentModal.vue";
import PlaylistModal from "@/Components/Modals/PlaylistModal.vue";
import VideoStreamMiniPlayer from "@/Components/Modals/MiniPlayers/VideoStreamMiniPlayer.vue";
import ShareModel from "@/Components/Modals/ShareModel.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import {usePage} from "@inertiajs/vue3";

const navStore = useNavStore();
const name = 'AuthenticatedLayout';
let showingNavigationDropdown = ref(false);



// navStore.showingStudioLinks = ref(props.isStudioRoute);

//about page computed
// const aboutPage = computed(() => {
//     return route().current('about');
// });
//
// const authPages = computed(() => {
//     // return
//     return true;
// });

// I'm sorry I couldn't figure it out, nothing worked

</script>

<template>



    <div>

        <!-- this is where the toast message popup is added -->
        <ToastList :flash="$page.props.flash"/>

        <div class=" flex flex-col   ">

            <Nav v-if="!(route().current('login') || route().current('register') || route().current('password.request') || route().current('password.reset') || route().current('password.email') || route().current('password.store'))"/>

            <!-- Page Content -->
            <main class="flex flex-row flex-grow    " >

                <div v-if="!route().current('about') && !(route().current('login') || route().current('register') || route().current('password.request') || route().current('password.reset') || route().current('password.email') || route().current('password.store'))"
                     class="pointer-events-none opacity-0 flex-shrink-0 transition  ease-in-out"  :class="{'sm:w-64  ': navStore.getNavigationDropdown(), 'sm:w-24': !navStore.getNavigationDropdown()}">

                </div>
                <div class="relative flex-shrink transition duration-700 ease-in-out w-full"  :class="{'   ': navStore.getNavigationDropdown()}">

                    <slot  />
                    <!--Modals we want centered with side bar-->
                    <PlaylistModal/>
                    <ShareModel/>
                    <ConfirmModal />
                </div>

                <!--Modals that don't matter how they are -->
                <VideoStreamMiniPlayer/>
                <VideoStreamModal/>

                <!--<CookieConsent/>-->

            </main>

        </div>
    </div>
</template>
