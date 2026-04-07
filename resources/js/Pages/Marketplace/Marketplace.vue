<script setup>
import Footer from "@/Components/General/Footer.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import {useAuthStore} from "../../Stores/AuthStore";

const name = "Marketplace"

const props = defineProps({
    products: Object,
    awards: Object,
});
</script>
<template>
    <SeoHead
        title="Marketplace"
        description="VidCoins and awards on VidGaze—support creators with virtual goods and explore marketplace features."
    />


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
                        your favorite contributors to keep making VidGaze better.

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





        <div class="flex flex-col">
            <!-- Page header -->
            <div class="relative h-screen w-screen bg-white    flex flex-col py-auto justify-center align-middle   px-4 sm:px-6 lg:px-8">
                <div class="py-24 px-auto w-full">
                    <p class="text-4xl text-zinc-900
                subheading w-full text-center">
                        Here’s what you can buy with VidCoins
                    </p>
                    <p class="text-zinc-900 text-lg w-full text-center px-14 py-4 lg:w-7/12 mx-auto">
                        Spend your coins on these Awards reserved exclusively for the finest VidGaze contributors.
                        Awarding a video, stream or comment highlights it for all to see, and makes them more likely to be recommended.
                    </p>
                    <div class="flex flex-col mt-10 gap-y-16 sm:gap-y-0 sm:flex-row px-16 justify-center gap-x-16">
                        <div v-for="award in awards" :key="award.id" class="w-full sm:w-1/3 md:w-64">
                            <img :src="award.icon_url" class="h-24 mx-auto" />
                            <p class="font-bold text-center text-zinc-900  subheading w-full mt-5 text-2xl">
                                {{ award.name }} Award
                            </p>
                            <p class="font-semibold text-zinc-900 text-center w-full mt-2 text-md">
                                Shows a {{ award.name }} Award on the video, stream or comment and ... that’s it. You’ll need {{ award.coin_price }} VidCoins.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="flex flex-col">
            <!-- Page header -->
            <div class="relative h-screen w-screen   flex flex-col py-auto justify-center align-middle   px-4 sm:px-6 lg:px-8">

                <div class="w-full   text-white py-16 object-fill bg-center bg-cover"   id="buy">
                    <p class="text-4xl font-bold uppercase w-full text-center">Buy VidCoins (Not Available) fuck fake currency</p>

                    <div class="flex flex-col gap-16 mx-16 my-16 items-center justify-center">
<!--                        <div v-for="product in products" :key="product.id" class="h-max sm:h-52 w-80 xs:w-96 sm:w-full-->
<!--                    bg-white dark:bg-zinc-800-->
<!--                    rounded lg:w-2/3 lg:mx-auto min-h-64 flex flex-col sm:flex-row overflow-hidden">-->
<!--                            <div class="py-5 w-full sm:w-2/5 flex flex-row items-center align-middle">-->
<!--                                <img :src="product.image_url" class="w-full max-h-44 mx-auto object-contain" />-->
<!--                            </div>-->
<!--                            <div class="w-full sm:w-3/5 flex flex-col justify-center">-->
<!--                                <div class="flex flex-col sm:flex-row pb-5 sm:py-10 px-5 xl:px-10">-->
<!--                                    <div class="w-full sm:w-2/3">-->
<!--                                        <p class="text-zinc-900 dark:text-white font-bold text-xl text-center sm:text-left">-->
<!--                                            {{ product.name }}-->
<!--                                        </p>-->
<!--                                        <p class="text-zinc-900 dark:text-white mt-1 text-center sm:text-left">-->
<!--                                            {{ product.description }}-->
<!--                                        </p>-->
<!--                                    </div>-->
<!--                                    <primary-button class="w-full sm:w-1/3 mt-5 sm:mt-0 mx-5">-->
<!--                                        Buy for £{{ (product.price / 100).toFixed(2) }}-->
<!--                                    </primary-button>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                        <div class="h-max sm:h-52 w-80 xs:w-96 sm:w-full-->
<!--                    bg-white dark:bg-zinc-800-->
<!--                    rounded lg:w-2/3 lg:mx-auto min-h-64 flex flex-col sm:flex-row overflow-hidden">-->
<!--                            <div class="py-5 w-full sm:w-2/5 flex flex-row items-center align-middle">-->
<!--                                <img src="/images/vidcoins/mascot/PushingCrate.png" class="w-full max-h-44 mx-auto object-contain" />-->
<!--                            </div>-->
<!--                            <div class="w-full sm:w-3/5 flex flex-col justify-center">-->
<!--                                <div class="flex flex-col sm:flex-row pb-5 sm:py-10 px-5 xl:px-10">-->
<!--                                    <div class="w-full sm:w-2/3">-->
<!--                                        <p class="text-zinc-900 dark:text-white font-bold text-xl text-center sm:text-left">-->
<!--                                            Any amount?-->
<!--                                        </p>-->
<!--                                        <p class="text-zinc-900 dark:text-white mt-1 text-center sm:text-left"-->
<!--                                           v-text="useAuthStore().user != null ? 'If you want to buy a specific amount of VidCoins, send direct to our Revolut handle @vidgaze and add this reference: ' + useAuthStore().user.creator.reference : 'Please log in if you want to buy a specific amount of VidCoins.'">-->
<!--                                        </p>-->
<!--                                    </div>-->
<!--                                    <primary-button class="w-full sm:w-1/3 mx-5 h-max my-auto text-center px-auto">-->
<!--                                        <p>Buy VidCoins</p>-->
<!--                                    </primary-button>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                        </div>-->
                    </div>
                </div>
            </div>
        </div>


            <div class="relative w-screen  bg-white dark:bg-vidgaze-blue    ">
            <Footer/>
        </div>

    </div>




</template>

