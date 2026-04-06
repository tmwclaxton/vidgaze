<script setup>
import {onMounted, onUnmounted, ref} from "vue";
import {usePage} from "@inertiajs/vue3";
import {useAuthStore} from "@/Stores/AuthStore";
import {shuffle} from "lodash";

const carouselItems = ref([]);
let autoplayId = null;

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

    carouselItems.value = [];

    // concatenate a few more sites
    carouselItems.value.push(
        {
            imgSrc: '/images/banners/VidGaze-Banner.png',
            link: "https://github.com/tmwclaxton/vidgaze" // TODO: update this link to the direction page later
        },
        // {
        //     imgSrc: '/images/banners/Freedom-Banner.png',
        //     link: "/" // TODO: update this link to the freedom tech category page later
        // },
        {
            imgSrc: '/images/banners/GrantGunnerBanner.png',
            link: 'https://grantgunner.org/'
        },
    );

    axios.get(route('api.creator.index', {featured: true}))
        .then(response => {
            response.data.creators.data.forEach((item) => {
                carouselItems.value.push({
                    imgSrc: item.banner_url,
                    link: route('channel.show', {slug: item.slug})
                });
            });

            if (carouselItems.value.length > 1) {
                // shuffle everything except the first item
                carouselItems.value = [carouselItems.value[0], ...shuffle(carouselItems.value.slice(1))];
            }
        })


    autoplayId = window.setInterval(() => {
        if (!isMouseOverCarousel.value && carouselWrapper.value) {
            scrollToNextItem();
        }
    }, 7000);
});

onUnmounted(() => {
    if (autoplayId !== null) {
        clearInterval(autoplayId);
    }
});
</script>

<template>
    <div
        class="relative group overflow-hidden w-full aspect-[21/9] max-h-[min(20rem,42vh)] min-h-[10rem] ring-1 ring-black/5 dark:ring-white/10 rounded-2xl"
        @mouseenter="handleMouseEnter"
        @mouseleave="handleMouseLeave"
    >
        <div
            ref="carouselWrapper"
            class="overflow-x-auto overflow-y-hidden snap-x snap-mandatory h-full w-full flex flex-row opacity-100 [&::-webkit-scrollbar]:hidden"
            style="scrollbar-width: none; -ms-overflow-style: none;"
        >
            <a
                v-for="(item, index) in carouselItems"
                :key="index"
                class="flex shrink-0 h-full w-full snap-center relative outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900"
                :href="item.link"
            >
                <img
                    :src="item.imgSrc"
                    alt=""
                    class="block w-full h-full object-cover cursor-pointer"
                    loading="lazy"
                />
            </a>
        </div>

        <div
            v-if="carouselItems.length > 1"
            class="absolute z-30 flex gap-2 -translate-x-1/2 bottom-4 left-1/2 pointer-events-none"
        >
            <button
                v-for="(item, index) in carouselItems"
                :key="index"
                type="button"
                class="pointer-events-auto h-2.5 w-2.5 rounded-full transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-black/20"
                :class="activeIndex === index
                    ? 'bg-white scale-110 ring-2 ring-white/40'
                    : 'bg-white/45 hover:bg-white/70'"
                :aria-label="`Slide ${index + 1}`"
                @click.prevent="scrollToItem(index)"
            />
        </div>

        <div
            v-if="carouselItems.length > 1"
            class="absolute top-0 left-0 z-10 flex items-center justify-center h-full pl-3 sm:pl-6 pointer-events-none"
        >
            <button
                type="button"
                class="without-ring cursor-pointer inline-flex items-center justify-center w-9 h-9 sm:w-12 sm:h-12 rounded-full pointer-events-auto bg-black/40 hover:bg-black/55 text-white opacity-90 sm:opacity-0 sm:group-hover:opacity-100 transition duration-200 ease-in-out backdrop-blur-[2px]"
                @click.prevent="scrollToPrevItem"
            >
                <font-awesome-icon :icon="['fas', 'chevron-left']" class="w-4 h-4 sm:w-5 sm:h-5" />
                <span class="sr-only">Previous</span>
            </button>
        </div>

        <div
            v-if="carouselItems.length > 1"
            class="absolute top-0 right-0 z-10 flex items-center justify-center h-full pr-3 sm:pr-6 pointer-events-none"
        >
            <button
                type="button"
                class="without-ring cursor-pointer inline-flex items-center justify-center w-9 h-9 sm:w-12 sm:h-12 rounded-full pointer-events-auto bg-black/40 hover:bg-black/55 text-white opacity-90 sm:opacity-0 sm:group-hover:opacity-100 transition duration-200 ease-in-out backdrop-blur-[2px]"
                @click.prevent="scrollToNextItem"
            >
                <font-awesome-icon :icon="['fas', 'chevron-right']" class="w-4 h-4 sm:w-5 sm:h-5" />
                <span class="sr-only">Next</span>
            </button>
        </div>
    </div>

</template>
