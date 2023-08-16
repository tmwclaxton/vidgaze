
<script setup>
import { Head } from '@inertiajs/vue3';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
import {onMounted, onUnmounted, ref} from "vue";
import TopStreamsRow from "@/Components/ContentRows/TopStreamsRow.vue";
import CategoriesRow from "@/Components/ContentRows/CategoriesRow.vue";
import {debounce} from "lodash";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
import CategoryRowWithStreams from "@/Components/ContentRows/CategoryRowWithStreams.vue";
const name = "StreamsIndex";
const contentRoutesStore = useContentRoutesStore();

const categories = ref([]);
const fetchCategories = async () => {
    categories.value = await contentRoutesStore.getCategories(8, null, true)
};

const categoriesForRows = ref([]);
const fetchCategoriesForRows = async () => {
    const categoryIds = categoriesForRows.value.map(category => category.id).join(',');
    categoriesForRows.value = categoriesForRows.value.concat(
        await contentRoutesStore.getCategories(8, categoryIds, false)
    );
};

const debouncedFetchCategoriesForRows = debounce(fetchCategoriesForRows, 500);

const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const bodyHeight = document.body.offsetHeight;
    // console.log(scrollPosition, bodyHeight)

    // check if user has reached the bottom of the page
    if (scrollPosition >= bodyHeight - 100) {
        // console.log('bottom of page')
        debouncedFetchCategoriesForRows(); // call the debounced version of fetch categories

    }
};

onMounted(async () => {
    window.addEventListener('scroll', handleScroll);
    await fetchCategories();
    await fetchCategoriesForRows();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});



</script>
<template>

    <div>
        <Head title="Popular Streams"/>

        <PaddingLayout class="-mt-4">

            <TopStreamsRow/>

            <CategoriesRow :categories="categories"/>

            <CategoryRowWithStreams v-for="(category, index) in categoriesForRows" :key="category.id" :category="category"/>

        </PaddingLayout>
    </div>

</template>
