<script setup>
import {onMounted, onUnmounted, ref} from "vue";
import RowDivider from "@/Components/General/RowDivider.vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import {debounce} from "lodash";

const name = 'Category';
const category = ref(null);
const streams = ref([]);
const props = defineProps({
    slug: {
        type: String,
        required: true,
    },
});

onMounted(async () => {
    await useContentRoutesStore().getCategory(props.slug).then((response) => {
        category.value = response;
    }).then(() => {
        getStreams();
        window.addEventListener('scroll', handleScroll);

    });
});

const getStreams = async () => {
    // if streams length is divisible by 12, then we can get more streams
    if (streams.value.length % 12 === 0) {
        await useContentRoutesStore().getStreams(12, category.value.id, streams.value.length).then((response) => {
            streams.value = streams.value.concat(response);
        });
    }
};

const debouncedGetStreams = debounce(getStreams, 500);

const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;
    // check if user has reached the bottom of the page
    if (scrollPosition >= bodyHeight - 100) {
        // console.log('bottom of page')
        debouncedGetStreams(); // call the debounced version of fetch categories

    }
};

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>
<template>
    <ConsistentPadding>
        <div v-if="category !== null" class="flex flex-col">
            <div class="flex flex-row ">
                <img v-if="category.thumbnail_url" class="h-48 shadow rounded" v-bind:src="category.thumbnail_url"/>
                <div class=" flex flex-col ml-3 ">
                    <span class="  font-bold text-4xl mb-1" v-text="category.name"/>
                    <!-- Category Tags -->
                    <div class="flex flex-row flex-wrap gap-2">
                        <div v-for="tag in category.tags" class="cursor-pointer px-3 p-1 rounded-full text-xs font-bold bg-zinc-200 dark:bg-zinc-700">
                            <p v-text="tag"/>
                        </div>
                    </div>
                    <div class="text-sm">
                        <p id="description" v-text="category.description"/>
                    </div>
                </div>
            </div>

            <row-divider/>

            <div class="px-5 pb-10">
                <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                        <VideoStreamCard v-for="stream in streams" :key="stream.id" :item="stream" :category_page="true"/>
                </div>
            </div>

        </div>
    </ConsistentPadding>
</template>
