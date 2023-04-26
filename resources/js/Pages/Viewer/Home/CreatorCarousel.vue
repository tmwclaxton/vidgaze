<script setup>
import {onMounted, ref} from "vue";

const carouselItems = [
    {
        imgSrc:
            "https://yt3.googleusercontent.com/MCKlDYo78cX-ODEurmP8J1q-Pkf27Sb2E0cD8kbgwDU8ZlmQVll7gLmbznsPrXvinS6577z-bA=w1707-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj",
    },
    {
        imgSrc:
            "https://yt3.googleusercontent.com/YYL2_SdOUsyoeHgeIdmy-pje47RTKWh95jMoJm8qY-g5Ib8yVPlUfarXJP6NPN_tUqOZkU1hgOo=w2120-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj",
    },
    {
        imgSrc:
            "https://yt3.googleusercontent.com/lns9gHx-jrwkKHjn5rm6leWtyPb-pBv3XsyxH3bdcwC9aXPK7EqcldpQBY7q6fYhchC0o2kbJQ=w1707-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj",
    },
    {
        imgSrc: "https://yt3.googleusercontent.com/eQLr0tOKbUf2UqOfIZ2WQGxXouOl3xxA8VN4bCjG9_WyduAvNYBWRv9nWkLrTBQ9rhx9YEH5mA=w2120-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj"
    }
];

const activeIndex = ref(0);
const carouselWrapper = ref(null);
const isMouseOverCarousel = ref(false);
function scrollToPrevItem() {
    const desiredIndex = activeIndex.value > 0 ? activeIndex.value - 1 : carouselItems.length - 1;
    scrollToItem(desiredIndex);
}

function scrollToNextItem() {
    const desiredIndex = activeIndex.value < carouselItems.length - 1 ? activeIndex.value + 1 : 0;
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
    // console.log(carouselWrapper.value);

    setInterval(() => {
        if (!isMouseOverCarousel.value && carouselWrapper.value) {
            scrollToNextItem();
        }
    }, 7000); // scroll every 10 seconds
});
</script>

<template>
    <div class="relative group overflow-hidden h-75 shadow-md  ">
        <!-- Carousel wrapper -->
        <div ref="carouselWrapper"
             @mouseenter="handleMouseEnter"
             @mouseleave="handleMouseLeave"
             class="overflow-y-hidden overflow-x-hidden snap-mandatory snap-x  h-full w-full flex flex-row relative transition-all delay-75 duration-700 ease-in-out  opacity-100 point-events-auto" >
            <!-- Item -->
            <div class="flex-shrink-0 h-full w-full relative snap-center  "
                 v-for="(item, index) in carouselItems"
                 :key="index"
                 :class="{'    ': activeIndex !== index,'  ': activeIndex === index}"
            >
                <img :src="item.imgSrc" class="block w-full h-full max-h-72" />

                 <!--<div class="absolute inset-0 bg-gradient-to-b from-transparent to-white/50 h-screen w-full"></div>-->
            </div>
        </div>
        <!-- Slider controls -->
        <div class="absolute z-30 flex space-x-3 -translate-x-1/2 bottom-5 left-1/2 ">

            <button @click="scrollToItem(index)"
                    class="w-3 h-3 rounded-full"
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
        <div class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-8 ">
            <span @click="scrollToPrevItem"
              class="cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded-full
              sm:w-16 sm:h-16 bg-vidgaze-blue/50 hover:bg-vidgaze-blue opacity-0 group-hover:opacity-100 transition duration-200 ease-in-out">
                <font-awesome-icon :icon="['fas', 'chevron-left']" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                <span class="sr-only">Previous</span>
            </span>
        </div>

        <!--next button-->
        <div class="absolute top-0 right-0 flex items-center justify-center h-full px-4">
            <span @click="scrollToNextItem"
                  class=" cursor-pointer inline-flex items-center justify-center w-8 h-8 rounded-full
                  sm:w-16 sm:h-16 bg-vidgaze-blue/50 hover:bg-vidgaze-blue opacity-0 group-hover:opacity-100 transition duration-200 ease-in-out">
                <font-awesome-icon :icon="['fas', 'chevron-right']" class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                <span class="sr-only">Next</span>
            </span>
        </div>
    </div>
</template>
