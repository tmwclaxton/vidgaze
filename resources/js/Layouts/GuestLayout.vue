<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative min-h-screen flex  pt-6 sm:pt-0 bg-vidgaze-blue">
        <div class="absolute  top-0 left-0 h-screen w-screen z-0 ">
            <img style="filter: brightness(0.6);" src="/images/logos/vidgaze/night_sky2.jpg" alt="very cool bg"
                 class="h-full w-full">
        </div>

        <div class="z-10 flex flex-col sm:justify-center items-center w-screen h-screen bg-gradient-to-b from-transparent to-gray-900  ">
            <div>
                <Link href="/">
                    <img src="/images/logos/vidgaze/vidgaze_banner.png" alt="VidGaze Logo"
                         class="h-20 w-auto">
                </Link>
            </div>

            <div
                class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-vidgaze-blue-nav   shadow-md overflow-hidden sm:rounded-lg"
            >
                <slot />
            </div>
        </div>

    </div>
</template>
