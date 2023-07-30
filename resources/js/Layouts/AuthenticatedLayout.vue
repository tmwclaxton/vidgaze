<script setup>
import {computed, onMounted, ref, watch} from 'vue';

import Nav from '@/Shared/Navigation/Nav.vue';
import ToastList from "@/Components/Toast/ToastList.vue";
import {useNavStore} from "@/Stores/NavStore";
import VideoStreamModal from "@/Components/Modals/ContentModal.vue";
import PlaylistModal from "@/Components/Modals/PlaylistModal.vue";
import VideoStreamMiniPlayer from "@/Components/Modals/MiniPlayers/VideoStreamMiniPlayer.vue";
import ShareModel from "@/Components/Modals/ShareModel.vue";
import ConfirmModal from "@/Components/Modals/ConfirmModal.vue";
import {usePage} from "@inertiajs/vue3";
import {usePlayerStore} from "@/Stores/PlayerStore";
import {useAuthStore} from "@/Stores/AuthStore";
const playerStore = usePlayerStore();
const authStore = useAuthStore();
const navStore = useNavStore();
const name = 'AuthenticatedLayout';
let showingNavigationDropdown = ref(false);

const loadScript = (src, id) => {
    if (!document.getElementById(id)) {
        const tag = document.createElement('script');
        tag.src = src;
        tag.id = id;
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }
};

// once 4 loadscripts are loaded update state of playerStore

const loadScripts = async () => {

};

onMounted(() => {

    loadScripts();
    // use authStore to get user
    if (authStore.user === null) {
        authStore.getUser();
    }


});

</script>

<template>



    <div>

        <!-- this is where the toast message popup is added -->
        <ToastList :flash="$page.props.flash"/>

        <div class=" flex flex-col   ">

            <Nav v-if="usePage().props.layoutDisplay !== 'auth'"/>

            <!-- Page Content -->
            <main class="flex flex-row flex-grow    " >

                <div v-if="usePage().props.layoutDisplay !== 'auth' && usePage().props.layoutDisplay !== 'wide'"
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
