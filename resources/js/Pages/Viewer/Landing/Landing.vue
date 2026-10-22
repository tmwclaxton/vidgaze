<script setup>
import {onMounted, onUnmounted, ref} from "vue";
import FounderCard from "@/Pages/Viewer/Landing/Partials/FounderCard.vue";
import Footer from "@/Components/General/Footer.vue";
import {useAuthStore} from "@/Stores/AuthStore";
import {router} from "@inertiajs/vue3";


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

    // if user goes to landing route '/' and logged in redirect to home route
    // if they arrive here via about route stay here
    if (route().current('landing') && useAuthStore().user ) {
        router.visit(route('home'));
    }


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

<template >
    <Head title="About" />
    <div class="-mt-16 "></div>


    <div class="fixed top-0 left-0 h-screen w-screen z-0">
        <img style="filter: brightness(0.6);" src="/images/logos/vidgaze/night_sky2.jpg" alt=""
             class="h-full w-full">
        <div class="absolute top-0 left-0 h-screen w-screen z-10  bg-gradient-to-b from-transparent to-gray-900"></div>
    </div>
    <div class="flex flex-col">
        <!-- Page header -->
        <div class="relative min-h-[70vh] w-full flex flex-col py-24 justify-center align-middle px-4 sm:px-6 lg:px-8">

            <div ref="welcomeMessageDiv" class=" transition duration-900 opacity-0 translate-y-52 ">
                <div class="target max-w-7xl mx-auto text-center pb-12 md:pb-6 flex flex-col gap-y-3  ">
                    <h1 class="text-4xl lg:text-5xl text-white font-bold">Welcome to VidGaze</h1>
                    <p class="text-2xl text-gray-200">Open-source social media, built in the open with its community.</p>
                    <p class="text-lg text-gray-300 max-w-2xl mx-auto">Watch creators from YouTube, Rumble and more in one place, ranked by the community instead of an algorithm.</p>
                </div>
                <!--get started button-->

                <div class="target cursor-pointer w-max mx-auto px-10 transition duration-300 ease-in-out">
                    <Link :href="route('home')" >
                        <div class="bg-transparent shine text-white font-semibold   py-2 px-20 border border-white  rounded ">
                            Get Started
                        </div>
                    </Link>

                </div>


            </div>
        </div>

        <!-- explain the platform -->

        <!--<div class="relative w-screen h-screen flex   ">-->

        <!--    <div class=" max-w-2xl w-full m-auto  p-4">-->
        <!--            <h2 class="text-4xl font-bold mb-8 mx-auto text-center text-white ">Message from the Founders</h2>-->
        <!--            <iframe class="w-full  my-auto ml-auto aspect-video rounded-2xl shadow-2xl shadow-zinc-900"-->
        <!--                src="https://www.youtube.com/embed/CIob-HllZOw" title="YouTube video player" frameborder="0"-->
        <!--                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"-->
        <!--                allowfullscreen></iframe>-->
        <!--    </div>-->

        <!--</div>-->

        <!--&lt;!&ndash; present the founders &ndash;&gt;-->
        <!--<div id="support" class="relative w-screen min-h-screen flex overflow-x-hidden  bg-white dark:bg-vidgaze-blue ">-->

        <!--    <div class="grid grid-cols-1 lg:grid-cols-2 m-auto py-8 ">-->
        <!--        <div class="max-w-4xl w-full  mx-auto px-4">-->
        <!--            <h2 class="text-4xl font-bold mb-8 mx-auto text-center ">The Team</h2>-->
        <!--            <div class="flex flex-col sm:flex-row gap-8 items-center justify-center p-14 ">-->
        <!--                <FounderCard img="/images/people/toby_3.jpeg" fullname="Toby Claxton"-->
        <!--                             description="Founder & Developer"-->
        <!--                             github="https://github.com/tmwclaxton"-->
        <!--                             linkedin="https://www.linkedin.com/in/toby-claxton/"-->
        <!--                             instagram="https://www.instagram.com/toby_claxton517/" />-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div id="support">-->
        <!--            <h2 class="text-4xl font-bold mb-8 mx-auto text-center ">Support Information</h2>-->
        <!--            <div class="flex flex-wrap flex-row gap-8 items-center justify-center p-14 w-full">-->
        <!--                <div class="flex flex-col gap-y-6 mt-2 w-full">-->
        <!--                    <div class="flex flex-row gap-x-4 mx-auto">-->
        <!--                        <font-awesome-icon :icon="['fas', 'envelope']"  class="h-8"/>-->
        <!--                        <p class="text-xl font-bold text-center "><a href="mailto:support@vidgaze.tv">support@vidgaze.tv</a></p>-->
        <!--                    </div>-->
        <!--                    <div class="flex flex-row gap-x-4 mx-auto ">-->
        <!--                        <font-awesome-icon :icon="['fas', 'phone']"  class="h-8"/>-->
        <!--                        <p class="text-xl font-bold text-center ">Phone: <a href="tel:+447478 635 635">+44 7837 370669</a></p>-->
        <!--                    </div>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->


        <!--</div>-->

        <!-- testimonials -->

        <div class="relative w-full flex py-16">

            <div class=" max-w-5xl w-full m-auto  p-4">
                <h2 class="text-4xl font-bold mb-8 mx-auto text-center text-white ">Testimonials</h2>

                <div class="max-w-5xl px-4 py-8 mx-auto">
                    <section class="bg-white dark:bg-vidgaze-blue rounded-lg">
                        <div class="grid grid-cols-1 gap-12 sm:grid-cols-3 sm:items-center">
                            <div class="relative flex  ">
                                <div class="mx-auto aspect-square w-full">
                                    <img
                                        src="https://pbs.twimg.com/profile_images/887313003246440448/FL2AZIi5_400x400.jpg"
                                        alt="Nick Brown"
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


        <div class="relative w-full bg-white dark:bg-vidgaze-blue">
         <Footer/>
        </div>

    </div>


</template>
