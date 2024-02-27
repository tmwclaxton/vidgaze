<script setup>
import { Link } from '@inertiajs/vue3';
import TopNavBar from "@/Shared/Navigation/TopNavBar.vue";
</script>

<template>
    <div class="top-0 absolute min-h-screen flex flex-col   sm:pt-0 bg-vidgaze-blue">

        <TopNavBar class="absolute top-0 w-full sm:hidden"/>

        <div class="absolute  top-0 left-0 h-screen w-screen z-0 ">
            <img style=" " src="/images/logos/vidgaze/night_sky2.jpg" alt="very cool bg"
                 class="h-full w-full">
        </div>

        <div class="z-10 flex flex-col sm:justify-center items-center w-screen h-screen bg-gradient-to-b from-transparent to-gray-900  ">
            <div class="hidden sm:flex ">
                <Link href="/">
                    <img src="/images/logos/vidgaze/vidgaze_banner.png" alt="VidGaze Logo"
                         class="h-20 w-auto ">
                </Link>
            </div>

            <div
                class="h-screen flex flex-col justify-center sm:h-max w-full sm:max-w-md sm:mt-6 px-6 py-4 bg-white dark:bg-vidgaze-blue-nav   shadow-md overflow-hidden sm:rounded-lg"
            >
                <slot />
            </div>
        </div>

    </div>
</template>
