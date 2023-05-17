
<script setup>
import { Head } from '@inertiajs/vue3';
import PaddingLayout from "@/Layouts/PaddingLayout.vue";
import {onMounted, onUnmounted, ref} from "vue";
import TopStreamsRow from "@/Components/ContentRows/TopStreamsRow.vue";
import CategoriesRow from "@/Components/ContentRows/CategoriesRow.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCard/VideoStreamSkeleton.vue";
import {debounce} from "lodash";
import RowDivider from "@/Components/ContentRows/Partials/RowDivider.vue";
import VideoStreamCard from "@/Components/Cards/VideoStreamCard/VideoStreamCard.vue";
import InfiniteCategoriesWithStreams from "@/Components/ContentRows/InfiniteCategoriesWithStreams.vue";

const categories = ref([]);
const fetchCategories = () =>  {
    axios.get(route('categories.index'),  {
        params: {
            perPage: 8,
        } } )
        .then(response => {
            setTimeout(() => {
                categories.value = response.data.data;
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};

const categoriesWithStreams = ref([]);
const fetchCategoriesWithStreams = () =>  {
    axios.get(route('categories.infinite'),  {
        params: {
            perPage: 8,
            categoryIds: categoriesWithStreams.value.map(item => item.category.id).join(',')
        } } )
        .then(response => {
            setTimeout(() => {
                categoriesWithStreams.value = categoriesWithStreams.value.concat(response.data);
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
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

    <Head title="Popular Streams" />

    <PaddingLayout class="-mt-4">

        <TopStreamsRow/>

        <CategoriesRow :categories="categories" />

        <InfiniteCategoriesWithStreams :categoriesWithStreams="categoriesWithStreams" />

    </PaddingLayout>

</template>
