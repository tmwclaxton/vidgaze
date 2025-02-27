
<script setup>

import {onMounted, onUnmounted, ref} from "vue";
import {debounce} from "lodash";
import ShortsSkeleton from "@/Components/Cards/ShortsCard/ShortsSkeleton.vue";
import ShortsCard from "@/Components/Cards/ShortsCard/ShortsCard.vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";

const name = 'TopShortsRow';
const shorts = ref([]);
const category = ref('popular');
const contentRoutesStore = useContentRoutesStore();
const fetchShorts = async () => {
    contentRoutesStore.getVideos(category.value, 8, [], true)
        .then(response => {
            setTimeout(() => {
                shorts.value = response;
                // when shorts are fetched, we need to compute the number of shorts to show based on screen size
                computedShorts.value = shorts.value.slice(0, shortsPerPage[getScreenSize()]);
            }, 500); // 500ms delay
        })

};
const shortsPerPage = {
    'xs': 2,
    "sm": 2,
    "md": 2,
    "ld": 2,
    "lg": 3,
    "xl": 4,
    "2xl": 8,
};
const getScreenSize = () => {
    const screenWidth = window.innerWidth;
    if (screenWidth < 470) {
        return "xs";
    } else if (screenWidth < 640) {
        return "sm";
    } else if (screenWidth < 768) {
        return "md";
    } else if (screenWidth < 868) {
        return "ld";
    } else if (screenWidth < 1024) {
        return "lg";
    } else if (screenWidth < 1280) {
        return "xl";
    } else {
        return "2xl";
    }
};
const computedShorts = ref([]);

onMounted(() => {
    window.addEventListener("resize", handleResize);
    fetchShorts();
});

onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
});

const handleResize = debounce(() => {
    const screenSize = getScreenSize();
    // check there enough shorts to show for the current screen size if not set to the max available
    if (shorts.value.length < shortsPerPage[screenSize]) {
        computedShorts.value = shorts.value;
        return;
    }
    computedShorts.value = shorts.value.slice(0, shortsPerPage[screenSize]);
}, 500);
</script>

<template>
    <div class="hid den md: flex flex-row gap-2  my-4 mb-8 ">
        <font-awesome-icon :icon="['fas', 'fire']"  class="my-auto h-6"/>
        <p class="font-bold text-2xl ">Rising Shorts</p>
    </div>

    <div class="hid den md: grid  gap-7 mx-10 grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-4 xl:grid-cols-8"  >
        <template  v-for="(short, index) in computedShorts" :key="short.id">
            <ShortsCard :item="short" />
        </template>
        <!--skeleton loading-->
        <template v-if="shorts.length === 0" v-for="i in 8">
            <ShortsSkeleton />
        </template>
    </div>



    <RowDivider />
</template>
