<script setup>
import {ref, watch} from 'vue';

import Nav from '@/Shared/Navigation/Nav.vue';
import ToastList from "@/Components/Toast/ToastList.vue";
import {useNavStore} from "@/Stores/NavStore";
import VideoStreamModal from "@/Components/Modals/ContentModal.vue";
import PlaylistModal from "@/Components/Modals/PlaylistModal.vue";
import ShareModel from "@/Components/Modals/ShareModel.vue";
const navStore = useNavStore();
const name = 'AuthenticatedLayout';
let showingNavigationDropdown = ref(false);

//accept props
const props = defineProps({
    showingStudioLinks: {
        type: Boolean,
        required: false,
        default: false
    },
});

watch(() => props.showingStudioLinks, (newVal) => {
    navStore.setStudioLinks(newVal);
});
</script>

<template>



    <div>
        <!-- this is where the toast message popup is added -->
        <ToastList :flash="$page.props.flash"/>

        <div class=" flex flex-col   ">

            <Nav/>

            <!-- Page Content -->
            <main class="flex flex-row flex-grow    " >

                <div v-if="!route().current('about')" class="pointer-events-none opacity-0 flex-shrink-0 transition  ease-in-out"  :class="{'sm:w-64  ': navStore.getNavigationDropdown(), 'sm:w-24': !navStore.getNavigationDropdown()}">

                </div>
                <div class="relative flex-shrink transition duration-700 ease-in-out w-full"  :class="{'   ': navStore.getNavigationDropdown()}">
                    <slot  />
                    <PlaylistModal/>
                    <ShareModel/>
                </div>
                <!--<CookieConsent/>-->
                <VideoStreamModal/>

            </main>

        </div>
    </div>
</template>
