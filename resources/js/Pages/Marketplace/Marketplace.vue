<script setup>
import Footer from "@/Components/General/Footer.vue";

const name = "Marketplace"
</script>
<template>
    <Head :title="'Marketplace'"/>


    <div class="fixed top-0 left-0 h-screen w-screen z-0">
        <img style="filter: brightness(0.6);" src="/images/logos/vidgaze/night_sky2.jpg" alt="very cool bg"
             class="h-full w-full">
        <div class="absolute top-0 left-0 h-screen w-screen z-10  bg-gradient-to-b from-transparent to-gray-900"></div>
    </div>

    <div class="flex flex-col">
        <!-- Page header -->
        <div class="relative h-screen w-screen     flex flex-col py-auto justify-center align-middle   px-4 sm:px-6 lg:px-8">

            <div class="flex flex-col md:flex-row text-white">
                <div class="w-full lg:w-3/4 lg:pl-6 flex flex-col justify-center ">
                    <p class="text-6xl md:text-8xl font-bold mb-6 text-center md:text-left lg:ml-8">VidCoins</p>
                    <p class="text-2xl font-semibold w-full md:w-4/5 lg:w-3/5 mb-10 lg:ml-8
                        leading-tight text-left">
                        VidCoins are a virtual good you can use to award exemplary videos, streams or comments. Support
                        VidGaze and encourage
                        your favorite contributors to keep making VidGaze better. &nbsp;Coming soon!

                    </p>
                </div>
                <div class="flex mx-auto ">
                    <div class="flex   align-middle items-center">
                        <img src="/images/vidcoins/mascot/ArmfulOfCoins.png"
                             class="  max-h-64 lg:max-h-96 mx-auto object-contain "/>
                    </div>
                </div>
            </div>
        </div>



        <!-- supported by -->
        <!--<div class="relative w-screen min-h-screen flex flex-col  bg-white dark:bg-vidgaze-blue  py-auto justify-center align-middle select-none ">-->

        <!--        &lt;!&ndash;coming soon in big letters&ndash;&gt;-->
        <!--        <div class="max-w-7xl mx-auto text-center pb-12 md:pb-6 flex flex-col gap-y-3  ">-->
        <!--            <h1 class="text-4xl lg:text-5xl text-white font-bold">Coming Soon</h1>-->
        <!--        </div>-->

        <!--</div>-->



        <div class="relative w-screen  bg-white dark:bg-vidgaze-blue    ">
            <Footer/>
        </div>

    </div>



</template>

