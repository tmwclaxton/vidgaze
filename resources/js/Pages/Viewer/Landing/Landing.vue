<script setup>
import {onMounted, onUnmounted, ref} from "vue";
import FounderCard from "@/Pages/Viewer/Landing/Partials/FounderCard.vue";
import Footer from "@/Components/General/Footer.vue";


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

};
</script>

<style>
.target {
    transition: opacity 0.6s ease;
}
</style>

<template >
    <Head title="Landing" />
    <div class="-mt-16 "></div>


    <div class="fixed top-0 left-0 h-screen w-screen z-0">
        <img style="filter: brightness(0.6);" src="/images/logos/vidgaze/night_sky2.jpg" alt="very cool bg"
             class="h-full w-full">
        <div class="absolute top-0 left-0 h-screen w-screen z-10  bg-gradient-to-b from-transparent to-gray-900"></div>
    </div>
    <div class="flex flex-col">
        <!-- Page header -->
        <div class="relative h-screen w-screen     flex flex-col py-auto justify-center align-middle   px-4 sm:px-6 lg:px-8">

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



        <!-- supported by -->
        <div class="relative w-screen min-h-screen flex  bg-white dark:bg-vidgaze-blue">

            <!--awards-->

            <div class="  m-auto  py-8">
                <div class="  mx-auto px-4">
                    <h2 class="text-4xl font-bold mb-4 mx-auto text-center ">Kindly Supported By</h2>
                    <div class="w-full mx-auto h-52 p-4 align-middle flex flex-row">
                        <img class=" mx-auto" src="https://media.licdn.com/dms/image/C5616AQF_LI7LB3LmFg/profile-displaybackgroundimage-shrink_200_800/0/1649275790532?e=2147483647&v=beta&t=J4ZNWNzLfsEGFmFyiAdtA0tQ2Xyq1RqKZYHBBdr0kGc"/>
                    </div>
                    <div class="flex flex-wrap flex-row items-center justify-center ">
                        <div class="   flex flex-row ">
                            <img class="h-52   object-contain" src="https://images.squarespace-cdn.com/content/v1/631a0fdf324dc751c8d24b33/b2ea62b4-5c04-409e-b01a-0727d468d6f6/CoFo_WithNavyBG_RGB.png?format=1500w"/>

                        </div>
                        <div class="  p-4 align-middle flex flex-row">
                            <img class="h-52   " src="https://www.scaleupinstitute.org.uk/wp-content/uploads/2019/11/techstart.png"/>
                        </div>
                        <div class="  align-middle flex flex-row">
                            <img class="h-52 " src="https://www.qub.ac.uk/home/media/Media,891905,smxx.png"/>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- explain the platform -->

        <div class="relative w-screen h-screen flex   ">

            <div class=" max-w-2xl w-full m-auto  p-4">
                    <h2 class="text-4xl font-bold mb-8 mx-auto text-center text-white ">Message from the Founders</h2>
                    <iframe class="w-full  my-auto ml-auto aspect-video rounded-2xl shadow-2xl shadow-zinc-900"
                        src="https://www.youtube.com/embed/CIob-HllZOw" title="YouTube video player" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>

        </div>

        <!-- present the founders -->
        <div id="support" class="relative w-screen min-h-screen flex  bg-white dark:bg-vidgaze-blue ">

            <div class="grid grid-cols-1 lg:grid-cols-2 m-auto py-8 ">
                <div class="max-w-4xl w-full  mx-auto px-4">
                    <h2 class="text-4xl font-bold mb-8 mx-auto text-center ">The Team</h2>
                    <div class="flex flex-col sm:flex-row gap-8 items-center justify-center p-14 ">
                        <FounderCard img="/images/people/toby_3.jpeg" fullname="Toby Claxton"
                                     description="Founder & Developer"
                                     github="https://github.com/tmwclaxton"
                                     linkedin="https://www.linkedin.com/in/toby-claxton/"
                                     instagram="https://www.instagram.com/toby_claxton517/" />
                        <FounderCard img="/images/people/josh_3.jpg" fullname="Joshua Young"
                                     description="Founder & Developer"
                                     github="https://github.com/joshuasy10"
                                     linkedin="https://www.linkedin.com/in/joshua-young-8a97911a2/"
                                     instagram="https://www.instagram.com/joshuasy1o/" />
                    </div>
                </div>
                <div id="support">
                    <h2 class="text-4xl font-bold mb-8 mx-auto text-center ">Support Information</h2>
                    <div class="flex flex-wrap flex-row gap-8 items-center justify-center p-14 w-full">
                        <div class="flex flex-col gap-y-6 mt-2 w-full">
                            <div class="flex flex-row gap-x-4 mx-auto">
                                <font-awesome-icon :icon="['fas', 'envelope']"  class="h-8"/>
                                <p class="text-xl font-bold text-center "><a href="mailto:support@vidgaze.tv">support@vidgaze.tv</a></p>
                            </div>
                            <div class="flex flex-row gap-x-4 mx-auto ">
                                <font-awesome-icon :icon="['fas', 'phone']"  class="h-8"/>
                                <p class="text-xl font-bold text-center ">Phone: <a href="tel:+447478 635 635">+44 7837 370669</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- testimonials -->

        <div class="relative w-screen h-screen flex   ">

            <div class=" max-w-5xl w-full m-auto  p-4">
                <h2 class="text-4xl font-bold mb-8 mx-auto text-center text-white ">Testimonials</h2>

                <div class="max-w-5xl px-4 py-8 mx-auto">
                    <section class="bg-white dark:bg-vidgaze-blue rounded-lg">
                        <div class="grid grid-cols-1 gap-12 sm:grid-cols-3 sm:items-center">
                            <div class="relative flex  ">
                                <div class="mx-auto aspect-square w-full">
                                    <img
                                        src="https://pbs.twimg.com/profile_images/887313003246440448/FL2AZIi5_400x400.jpg"
                                        alt=""
                                        class="object-cover rounded-l-lg w-full"
                                    />
                                </div>

                            </div>

                            <blockquote class="sm:col-span-2 text dark:textDark p-8 ">
                                <p class="text-xl font-semibold sm:text-2xl">
                                    “YouTube has a monopoly of medium and long form content - I like how this is a safety net for creators; this could be really useful”
                                </p>

                                <cite class="inline-flex items-center mt-8 not-italic">
                                    <span class="hidden w-6 h-px bg-gray-400 sm:inline-block"></span>
                                    <a href="https://www.instagram.com/lorecraftr/?hl=en"
                                       class="hover:underline text-sm text dark:textDark font-semibold uppercase sm:ml-3">
                                        <strong>Nick Brown</strong>, LoreCraft
                                    </a>
                                </cite>
                            </blockquote>
                        </div>
                    </section>
                </div>
            </div>
        </div>


        <div class="relative w-screen  bg-white dark:bg-vidgaze-blue    ">
         <Footer/>
        </div>

    </div>


</template>
