
<script setup>
import { Head } from '@inertiajs/vue3';
import PaddingLayout from "@/Layouts/PaddingLayout.vue";
import Carousel from "@/Pages/Viewer/Streams/Partials/Carousel.vue";
import { loadScript } from "vue-plugin-load-script";
loadScript("https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY")
    .then(() => {
        // Script is loaded, do something
    })
    .catch(() => {
        // Failed to fetch script
    });


</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
};
</script>
<template>

    <Head title="Popular Streams" />

    <div class="w-full grid grid-cols-1 sm:grid-cols-2">
        <div class="flex flex-col">
            <div id="player" class="bg-black h-96">
                <!-- Add a placeholder for the Twitch embed -->
            </div>
            <div class="h-52 font-bold text-4xl p-5">MonsterCat</div>
        </div>
        <div class="h-52"></div>
    </div>
    <PaddingLayout>
        <Carousel></Carousel>

    </PaddingLayout>

</template>
