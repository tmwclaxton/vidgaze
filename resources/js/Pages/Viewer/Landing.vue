<script setup>
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
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
    props: {
        showingStudioLinks: {
            type: Boolean,
            required: true
        }
    }
};
</script>

<style>
.target {
    transition: opacity 0.6s ease;
}
</style>

<template >
    <!--    <Head title="Landing" />-->
        <div class="-mt-16 "></div>


        <div class="fixed top-0 left-0 h-screen w-screen z-0">
            <img style="filter: brightness(0.6);" src="/images/logos/vidgaze/night_sky2.jpg" alt="very cool bg"
                 class="h-full w-full">
        </div>
            <scroll-parallax :speed="0.6">
                <!-- Page header -->
                <div
                    class="relative h-screen w-screen  bg-gradient-to-b from-transparent to-gray-900   flex flex-col py-auto justify-center align-middle   px-4 sm:px-6 lg:px-8">

                   <div ref="welcomeMessageDiv" class=" transition duration-900 opacity-0 translate-y-52">
                        <div class="target max-w-7xl mx-auto text-center pb-12 md:pb-6 flex flex-col gap-y-3  ">
                            <h1 class="text-4xl lg:text-5xl text-white font-bold">Welcome to VidGaze</h1>
                            <p class="text-2xl text-gray-300"> The ultimate video-streaming platform for both creators and
                                viewers alike </p>
                        </div>
                       <!--get started button-->

                        <div class="target cursor-pointer w-max mx-auto px-10 transition duration-300 ease-in-out">
                            <Link :href="route('home')" >
                                <div class="bg-transparent shine text-white font-semibold  py-2 px-20 border border-white  rounded ">
                                    Get Started
                                </div>
                            </Link>

                        </div>


                   </div>
                </div>

            </scroll-parallax>


            <!-- explain the platform-->
            <scroll-parallax :speed="0.3">
                <div class="w-screen h-screen flex  bgLight dark:bg-vidgaze-blue">

                    <!--awards-->

                    <div class="  m-auto  py-4">
                        <div class="container mx-auto px-4">
                            <h2 class="text-4xl font-bold mb-4 mx-auto text-center ">Kindly Supported By</h2>
                            <div class="flex flex-wrap justify-center">
                                <div class="w-1/2 sm:w-1/4 p-4 align-middle flex flex-row">
                                    <img class="w-64 mx-auto object-contain" src="https://images.squarespace-cdn.com/content/v1/631a0fdf324dc751c8d24b33/b2ea62b4-5c04-409e-b01a-0727d468d6f6/CoFo_WithNavyBG_RGB.png?format=1500w"/>

                                </div>
                                <div class="w-1/2 sm:w-1/4 p-4 align-middle flex flex-row">
                                    <img class="w-64 mx-auto object-contain" src="https://www.scaleupinstitute.org.uk/wp-content/uploads/2019/11/techstart.png"/>
                                </div>
                                <div class="w-1/2 sm:w-1/4 p-4 align-middle flex flex-row">
                                    <img class="w-64 mx-auto object-contain" src="https://www.qub.ac.uk/home/media/Media,891905,smxx.png"/>
                                </div>
                                <div class="w-1/2 sm:w-1/4 p-4 align-middle flex flex-row">
                                    <img class="w-64 mx-auto object-contain" src="https://www.qub.ac.uk/home/media/Media,891905,smxx.png"/>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </scroll-parallax>

            <!--   explain the platform-->
            <scroll-parallax :speed="0.3">

                <div
                    class="w-screen h-screen flex bg-gradient-to-b to-transparent from-gray-900  ">


                    <!--<div class="max-w-7xl m-auto  px-4 sm:px-6 lg:px-8">-->
                    <!--    <div class="flex flex-col gap-y-8  ">-->
                    <!--        <h2 class="text-4xl text dark:textDark font-bold">Watch all your favourite content in one place!</h2>-->
                    <!--        <p class="text-2xl text-center text dark:textDark ">YouTube, Twitch, Dailymotion, Odysee, Vimeo and more!-->
                    <!--            <br>Watch these and more at VidGaze today! </p>-->
                    <!--    </div>-->
                    <!--</div>-->

                    <!--<div class="max-w-7xl m-auto  px-4 sm:px-6 lg:px-8">-->
                    <!--    <div class="flex flex-col gap-y-8 text-center">-->
                    <!--        <h2 class="text-4xl text-white font-bold">Support your favourite content creators while your at it!</h2>-->
                    <!--        <p class="text-2xl text-zinc-300">-->
                    <!--            VidGaze can improve monetisation rates for creators-->
                    <!--            and gives subscribers the ability to support their favorite creators without giving up-->
                    <!--            their favourite content.&nbsp;&nbsp;-->
                    <!--            When multiple platforms have the same videos, VidGaze automatically selects the-->
                    <!--            source that pays the most to the creator. This means that creators can earn more money.-->
                    <!--        </p>-->
                    <!--    </div>-->
                    <!--</div>-->
                </div>
            </scroll-parallax>


</template>
