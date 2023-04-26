<script setup>

import {onMounted, onUnmounted, ref, watch} from "vue";
import TopNavBar from "@/Shared/Navigation/TopNavBar.vue";
import SideBar from "@/Shared/Navigation/SideBar.vue";
import {useNavStore} from "@/Stores/NavStore";
const navStore = useNavStore();

onMounted(() => {
    navStore.handleResize();
    window.addEventListener('resize', navStore.handleResize)

})

onUnmounted(() => {
    window.removeEventListener('resize', navStore.handleResize)
})




</script>

<template>
    <div class="h-16">
        <!--Nav is fixed so lets space things below-->

    </div>
    <nav class=" fixed z-40 top-0 w-full pointer-events-none">
        <!-- Primary Navigation Menu -->

            <TopNavBar />

            <SideBar/>

    </nav>

</template>

