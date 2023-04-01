<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ScrollParallax from 'vue3-parallax/src/components/ScrollParallax.vue';
import SearchIcon from '~/images/icons/search.svg';
import {onMounted, onUnmounted, ref} from "vue";


// this changes the opacity of the elements with the class "target" as you scroll
let opacity = 1;
const maxOpacity = 1;
const minOpacity = 0.2;
const updateOpacity = () => {
    const scrollPosition =
        document.documentElement.scrollTop || document.body.scrollTop;
    const maxScrollPosition =
        document.documentElement.scrollHeight - window.innerHeight;
    const newOpacity =
        maxOpacity - ((scrollPosition / maxScrollPosition)   * (maxOpacity - minOpacity) * 7)  ;

    opacity = newOpacity;
    document.querySelectorAll(".target").forEach((el) => {
        el.style.opacity = opacity;
    });
    // console.log(opacity);
};

const welcomeMessageDiv = ref(null);

onMounted(() => {
    window.addEventListener("scroll", updateOpacity);
    //wait for 2 seconds before removing the opacity-0 class
    setTimeout(() => {
        welcomeMessageDiv.value.classList.remove('opacity-0')
        welcomeMessageDiv.value.classList.remove('translate-y-52')
    }, 200)
});

onUnmounted(() => {
    window.removeEventListener("scroll", updateOpacity);
});


</script>
<style>
.target {
    transition: opacity 0.6s ease;
}
</style>

<template>
    <!--    <Head title="Landing" />-->
    <AuthenticatedLayout>
        <div class="-mt-16 "></div>
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

        <div class="fixed top-0 left-0 h-screen w-screen z-0">
            <img style="filter: brightness(0.6);" src="/images/logos/vidgaze/night_sky2.jpg" alt="very cool bg"
                 class="h-full w-full">
        </div>
            <scroll-parallax :speed="0.4">
                <!-- Page header -->
                <div
                    class="relative h-screen w-screen bg-gradient-to-b from-transparent to-gray-900   flex flex-col py-auto justify-center align-middle   px-4 sm:px-6 lg:px-8">

                   <div ref="welcomeMessageDiv" class=" transition duration-900 opacity-0 translate-y-52">
                        <div class="target max-w-7xl mx-auto text-center pb-12 md:pb-6 flex flex-col gap-y-3  ">
                            <h1 class="text-4xl lg:text-5xl text-white font-bold">Welcome to VidGaze</h1>
                            <p class="text-2xl text-gray-300"> The ultimate video-streaming platform for both creators and
                                viewers alike </p>
                        </div>
                        <!-- Search -->

                        <div class="target w-full max-w-xl mx-auto px-10">
                            <div class="relative w-full">
                                <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                                    <SearchIcon class="h-5 w-5 text-gray-400"/>
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


            <!-- explain the platform-->
            <scroll-parallax :speed="0.3">
                <div class="w-screen h-screen flex  bgLight dark:bg-vidgaze-blue">
                    <div class="max-w-7xl m-auto  px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col gap-y-8  ">
                            <h2 class="text-4xl text dark:textDark">What is VidGaze?</h2>
                            <p class="text-2xl  text dark:textDark ">VidGaze is a video-streaming platform that gives
                                creators the benefits of small platforms without losing the audience of the big
                                platforms. &nbsp;
                                VidGaze can improve monetisation rates for creators
                                and gives subscribers the ability to support their favorite creators without giving up
                                their favourite content. </p>
                        </div>
                    </div>
                </div>
            </scroll-parallax>

            <!--   explain the platform-->
            <scroll-parallax :speed="0.3">
                <div
                    class="w-screen h-screen flex bg-gradient-to-b to-transparent from-gray-900 dark:from-transparent ">
                    <div class="max-w-7xl m-auto  px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col gap-y-8">
                            <h2 class="text-4xl text-white">How does it work?</h2>
                            <p class="text-2xl text-zinc-300">
                                When multiple platforms have the same videos, VidGaze automatically selects the
                                source that pays the most to the creator. This means that creators can earn more money.
                            </p>
                        </div>


                    </div>
                </div>
            </scroll-parallax>

            <!-- explain the platform-->
            <scroll-parallax :speed="0.3">
                <div class="w-screen h-screen flex bgLight dark:bg-vidgaze-blue">
                    <div class="max-w-7xl m-auto  px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col gap-y-8  ">
                            <h2 class="text-4xl text dark:textDark ">What is VidGaze?</h2>
                            <p class="text-2xl text dark:textDark ">VidGaze is a video-streaming platform that gives
                                creators the benefits of small platforms without losing the audience of the big
                                platforms. &nbsp;
                                VidGaze can improve monetisation rates for creators
                                and gives subscribers the ability to support their favorite creators without giving up
                                their favourite content. </p>
                        </div>
                    </div>
                </div>
            </scroll-parallax>
    </AuthenticatedLayout>
</template>
