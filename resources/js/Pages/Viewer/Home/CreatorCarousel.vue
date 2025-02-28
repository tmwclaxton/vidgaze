<script setup>
import {onMounted, ref} from "vue";
import {usePage} from "@inertiajs/vue3";
import {useAuthStore} from "@/Stores/AuthStore";

const carouselItems = ref([]);

const activeIndex = ref(0);
const carouselWrapper = ref(null);
const isMouseOverCarousel = ref(false);
const scrollToPrevItem = () => {
    const desiredIndex = activeIndex.value > 0 ? activeIndex.value - 1 : carouselItems.value.length - 1;
    scrollToItem(desiredIndex);
}

const scrollToNextItem = () => {
    const desiredIndex = activeIndex.value < carouselItems.value.length - 1 ? activeIndex.value + 1 : 0;
    scrollToItem(desiredIndex);
}

function scrollToItem(index) {
    const itemWidth = carouselWrapper.value.clientWidth;
    const maxScrollPosition =
        carouselWrapper.value.scrollWidth - carouselWrapper.value.clientWidth;
    const desiredScrollPosition = Math.min(maxScrollPosition, index * itemWidth);
    carouselWrapper.value.scrollTo({
        left: desiredScrollPosition,
        behavior: "smooth",
    });
    activeIndex.value = index;
}

function handleMouseEnter() {
    isMouseOverCarousel.value = true;
}

function handleMouseLeave() {
    isMouseOverCarousel.value = false;
}


onMounted(() => {
    // if user is logged in, don't show join vidgaze banner
    // if (!useAuthStore().user) {
    //     carouselItems.value = [
    //         {
    //             imgSrc: '/images/banners/join_vidgaze.png',
    //             link: route('register')
    //         },
    //     ];
    // }

    // concatenate a few more sites
    carouselItems.value.push(
        {
            imgSrc: '/images/banners/VidGaze-Banner.png',
            link: "" // TODO: update this link to the direction page later
        },
        {
            imgSrc: '/images/banners/Freedom-Banner.png',
            link: "" // TODO: update this link to the freedom tech category page later
        },

        {
            imgSrc: '/images/banners/LAS-Banner.png',
            link: "https://www.lightningarbitragesolutions.com/"
        },
    );

    // shuffle the carousel items
    carouselItems.value = carouselItems.value.sort(() => Math.random() - 0.5);

    axios.get(route('api.creator.index', {featured: true}))
        .then(response => {
            response.data.creators.data.forEach((item) => {
                carouselItems.value.push({
                    imgSrc: item.banner_url,
                    link: route('channel.show', {slug: item.slug})
                });
            });
        })
        .catch(error => {
            console.log(error);
        } );


    setInterval(() => {
        if (!isMouseOverCarousel.value && carouselWrapper.value) {
            scrollToNextItem();
        }
    }, 7000); // scroll every 10 seconds


});
</script>

<template>
    <div class="relative group overflow-hidden h-75 shadow-md  w-full"
         @mouseenter="handleMouseEnter"
         @mouseleave="handleMouseLeave">
        <!-- Carousel wrapper -->
        <div ref="carouselWrapper"
             class="overflow-y-hidden overflow-x-hidden snap-mandatory snap-x  h-full w-full flex flex-row relative transition-all delay-75 duration-700 ease-in-out  opacity-100 point-events-auto" >
            <!-- Item -->
            <a class="flex-shrink-0 h-full w-full relative snap-center  "
                 v-for="(item, index) in carouselItems"
                 :key="index"
                 :class="{'    ': activeIndex !== index,'  ': activeIndex === index}"
                    :href="item.link"
            >
                <img :src="item.imgSrc" class="block w-full h-full max-h-72 cursor-pointer" />

                 <!--<div class="absolute inset-0 bg-gradient-to-b from-transparent to-white/50 h-screen w-full"></div>-->
            </a>
        </div>
        <!-- Slider controls -->
        <div class="absolute z-30 flex space-x-3 -translate-x-1/2 bottom-5 left-1/2 pointer-events-none cursor-pointer ">

            <button @click="scrollToItem(index)"
                    class="w-3 h-3 rounded-full pointer-events-auto"
                    v-for="(item, index) in carouselItems"
                    :key="index"
                    type="button"
                    :class="{
                      'bg-gray-400': activeIndex !== index,
                      'bg-white': activeIndex === index
                    }" >
            </button>
        </div>

        <!-- previous button -->
        <div class="absolute top-0 left-0 z-10 flex items-center justify-center h-full px-8 pointer-events-none">
            <span @click="scrollToPrevItem"
              class="cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded-full pointer-events-auto
              sm:w-16 sm:h-16 bg-vidgaze-blue/50 hover:bg-vidgaze-blue opacity-0 group-hover:opacity-100 transition duration-200 ease-in-out">
                <font-awesome-icon :icon="['fas', 'chevron-left']" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                <span class="sr-only">Previous</span>
            </span>
        </div>

        <!--next button-->
        <div class="absolute top-0 right-0 flex items-center justify-center h-full px-8 pointer-events-none">
            <span @click="scrollToNextItem"
                  class=" cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded-full pointer-events-auto
                  sm:w-16 sm:h-16 bg-vidgaze-blue/50 hover:bg-vidgaze-blue opacity-0 group-hover:opacity-100 transition duration-200 ease-in-out">
                <font-awesome-icon :icon="['fas', 'chevron-right']" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                <span class="sr-only">Next</span>
            </span>
        </div>
    </div>
</template>
