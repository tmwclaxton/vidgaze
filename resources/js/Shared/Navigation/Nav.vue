<script setup>

import {onMounted, onUnmounted} from "vue";
import TopNavBar from "@/Shared/Navigation/TopNavBar.vue";
import SideBar from "@/Shared/Navigation/SideBar.vue";
import {useNavStore} from "@/Stores/NavStore";
import BottomNavBar from "@/Shared/Navigation/BottomNavBar.vue";
const navStore = useNavStore();

function onGlobalKeydown(e) {
    if (e.key !== "Escape") {
        return;
    }
    if (navStore.getNavigationDropdown()) {
        navStore.closeNavigationDropdown();
    }
    if (navStore.getExpandedSearchResults()) {
        navStore.toggleExpandedSearchResultsOff();
    }
}

onMounted(() => {
    navStore.handleResize();
    window.addEventListener("resize", navStore.handleResize);
    document.addEventListener("keydown", onGlobalKeydown);
})

onUnmounted(() => {
    window.removeEventListener("resize", navStore.handleResize);
    document.removeEventListener("keydown", onGlobalKeydown);
})




</script>

<template>
    <div class="h-16">
        <!--Nav is fixed so lets space things below-->

    </div>
    <nav class="fixed top-0 z-40 w-full pointer-events-none" aria-label="Primary">
        <TopNavBar />

        <!-- Scrim: stronger on mobile, lighter on desktop -->
        <div
            v-show="navStore.getNavigationDropdown()"
            class="pointer-events-auto fixed inset-0 z-[35] bg-black/50 backdrop-blur-[2px] transition-opacity duration-200 sm:bg-black/35 sm:backdrop-blur-sm"
            aria-hidden="true"
            @click="navStore.closeNavigationDropdown()"
        />

        <SideBar/>
    </nav>

</template>

