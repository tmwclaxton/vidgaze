
<script setup>
import { Head } from '@inertiajs/vue3';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
import {onMounted, onUnmounted, ref} from "vue";
import TopStreamsRow from "@/Components/ContentRows/TopStreamsRow.vue";
import CategoriesRow from "@/Components/ContentRows/CategoriesRow.vue";
import {debounce} from "lodash";
import InfiniteCategoriesWithStreams from "@/Components/ContentRows/InfiniteCategoriesWithStreams.vue";
import {useContentRoutesStore} from "@/Stores/ContentRoutesStore";
const name = "StreamsIndex";
const categories = ref([]);
const contentRoutesStore = useContentRoutesStore();
const fetchCategories = async () => {
    await contentRoutesStore.getCategories(8)
        .then(response => {
            setTimeout(() => {
                console.log(response);
                categories.value = response.data.categories.data;
            }, 500); // 500ms delay
        })
};

const categoriesForRows = ref([]);
const fetchCategoriesForRows = async () => {
    await contentRoutesStore.getCategories(8, categoriesForRows.value.map(item => item.id))
        .then(response => {
            setTimeout(() => {
                categoriesForRows.value = categoriesForRows.value.concat(response.data);
            }, 100); // 500ms delay
        });

};

const debouncedFetchCategoriesForRows = debounce(fetchCategoriesForRows, 500);

onMounted(async () => {
    window.addEventListener('scroll', handleScroll1);
    await fetchCategories();
    await fetchCategoriesForRows();
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
        debouncedFetchCategoriesForRows(); // call the debounced version of fetch categories

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

            <!--<InfiniteCategoriesWithStreams :categoriesForRows="categoriesForRows"/>-->

        </PaddingLayout>
    </div>

</template>
