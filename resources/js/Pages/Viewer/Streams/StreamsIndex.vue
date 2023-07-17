
<script setup>
import { Head } from '@inertiajs/vue3';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
import {onMounted, onUnmounted, ref} from "vue";
import TopStreamsRow from "@/Components/ContentRows/TopStreamsRow.vue";
import CategoriesRow from "@/Components/ContentRows/CategoriesRow.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamSkeleton.vue";
import {debounce} from "lodash";
import RowDivider from "@/Components/General/RowDivider.vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCards/VideoStreamCard/VideoStreamCard.vue";
import InfiniteCategoriesWithStreams from "@/Components/ContentRows/InfiniteCategoriesWithStreams.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
const name = "StreamsIndex";
const categories = ref([]);
const contentRoutesStore = useContentRoutesStore();
const fetchCategories = async () => {
    await contentRoutesStore.getCategories(8)
        .then(response => {
            setTimeout(() => {
                categories.value = response.data.data;
            }, 500); // 500ms delay
        })
};

const categoriesWithStreams = ref([]);
const fetchCategoriesWithStreams = async () => {
    await contentRoutesStore.getCategoriesWithStreams(8, categoriesWithStreams.value.map(item => item.category.id).join(','))
        .then(response => {
            setTimeout(() => {
                categoriesWithStreams.value = categoriesWithStreams.value.concat(response.data);
            }, 100); // 500ms delay
        });

};

const debouncedFetchCategoriesWithStreams = debounce(fetchCategoriesWithStreams, 500);

onMounted(async () => {
    window.addEventListener('scroll', handleScroll1);
    await fetchCategories();
    await fetchCategoriesWithStreams();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll1);
});

const handleScroll1 = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;
    // console.log(scrollPosition, bodyHeight)

    // check if user has reached the bottom of the page
    if (scrollPosition >= bodyHeight - 100) {
        // console.log('bottom of page')
        debouncedFetchCategoriesWithStreams(); // call the debounced version of fetch categories

    }
};


</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
};
</script>
<template>

    <div>
        <Head title="Popular Streams"/>

        <PaddingLayout class="-mt-4">

            <TopStreamsRow/>

            <CategoriesRow :categories="categories"/>

            <InfiniteCategoriesWithStreams :categoriesWithStreams="categoriesWithStreams"/>

        </PaddingLayout>
    </div>

</template>
