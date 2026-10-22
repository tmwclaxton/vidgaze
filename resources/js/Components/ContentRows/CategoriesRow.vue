<script setup>
import CategorySkeleton from "@/Components/Cards/CategoryCard/CategorySkeleton.vue";
import CategoryCard from "@/Components/Cards/CategoryCard/CategoryCard.vue";
import RowDivider from "@/Components/General/RowDivider.vue";

const name = 'CategoriesRow';
const props = defineProps({
    categories: {
        type: Array,
        required: false,
        default: null
    },
    // true once the categories request finished, so an empty list hides the row
    loaded: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <template v-if="!(Array.isArray(categories) && categories.length === 0 && loaded)">
    <div class="flex flex-row gap-2  my-4 mb-8 ">
        <font-awesome-icon :icon="['fas', 'fire']"  class="my-auto h-6"/>
        <h2 class="font-bold text-2xl ">Categories we think you will like</h2>
    </div>
    <div class="grid grid-cols-4 xs:grid-cols-4 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-8 gap-4 w-full">
        <template v-for="category in categories" v-if="categories != null">
            <CategoryCard :category="category"/>
        </template>
        <!--skeleton loading-->
        <template v-if="!loaded && (!categories || categories.length === 0)" v-for="i in 8">
            <CategorySkeleton />
        </template>
    </div>


    <RowDivider />
    </template>
</template>



