<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import toast from "@/Stores/toast";
import ScrollParallax from 'vue3-parallax/src/components/ScrollParallax.vue';
import SearchIcon from '~/images/icons/search.svg';
const submit = () => {
    form.post("/test");
}

const addToast = () => {
    toast.add({
        message: "Hello from dashboard",
    });
}
</script>


<template>
<!--    <Head title="Landing" />-->
    <AuthenticatedLayout>
<!--        <template #header>-->
<!--            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>-->
<!--        </template>-->
        <div class="-mt-16"></div>

        <scroll-parallax speed="0.3" >
            <img style="filter: brightness(0.6);" src="/images/logos/vidgaze/night_sky2.jpg" alt="very cool bg" class="h-screen w-screen">

            <div class="absolute top-0 left-0 h-screen w-screen bg-gradient-to-b from-transparent to-gray-900 flex">
                <div class="max-w-7xl m-auto px-4 sm:px-6 lg:px-8  ">

                    <!-- Page header -->
                    <div class="max-w-7xl mx-auto text-center pb-12 md:pb-8 flex flex-col gap-y-3">
                        <h1 class="text-4xl text-white">Welcome to VidGaze</h1>
                        <p class="text-2xl text-gray-300"> The ultimate video-streaming platform for both creators and viewers alike </p>
                    </div>
<!--                     Search -->
                    <div class="max-w-xl mx-auto">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                                <SearchIcon class="h-5 w-5 text-gray-400" />
                            </div>
                            <input
                                type="text"
                                class="form-input w-full pl-10 pr-4 py-2 border border-transparent rounded-md leading-5 bg-gray-800 bg-opacity-25
                                text-white   text-sm sm:text-lg
                                placeholder-gray-400 focus:outline-none focus:bg-white focus:bg-opacity-10 focus:placeholder-gray-300 focus:text-gray-300 focus:ring-0 transition duration-150 ease-in-out"
                                placeholder="Search for a creator "
                            />
                        </div>
                    </div>


                </div>
            </div>

        </scroll-parallax>

        <scroll-parallax speed="0.3" >
            <!--   explain the platform-->
            <div class="w-screen h-screen flex " >
                <div class="max-w-7xl m-auto  px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-y-8  ">
                        <h2 class="text-4xl text ">What is VidGaze?</h2>
                        <p class="text-2xl text ">VidGaze is a video-streaming platform that gives creators the benefits of small platforms without losing the audience of the big platforms. &nbsp;
                            VidGaze can improve monetisation rates for creators
                            and gives subscribers the ability to support their favorite creators without giving up their favourite content. </p>
                    </div>
                </div>
            </div>
        </scroll-parallax>


<!--        <div class="mt-80">-->
<!--            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">-->
<!--                <div class="  overflow-hidden shadow-sm sm:rounded-lg">-->
<!--                    <div class="p-6 text-gray-900 dark:text-gray-100">You're logged in!</div>-->
<!--                    &lt;!&ndash;<ClockSVG class="w-5 fill-blue-400"/>&ndash;&gt;-->
<!--                    <div v-if="$page.props.flash.success" class="alert">-->
<!--                        {{ $page.props.flash.success }}-->
<!--                    </div>-->

<!--                    <button @click="addToast">Add toast</button>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
    </AuthenticatedLayout>
</template>
