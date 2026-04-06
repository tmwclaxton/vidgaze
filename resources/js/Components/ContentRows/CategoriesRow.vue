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
    }
});
</script>

<template>
    <div class="flex flex-row gap-2  my-4 mb-8 ">
        <font-awesome-icon :icon="['fas', 'fire']"  class="my-auto h-6"/>
        <p class="font-bold text-2xl ">Categories  we think you will like</p>
    </div>
    <div class="grid grid-cols-4 xs:grid-cols-4 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-8 gap-4 w-full">
        <template v-for="category in categories" v-if="categories != null">
            <CategoryCard :category="category"/>
        </template>
        <!--skeleton loading (~2 rows at lg:grid-cols-8) -->
        <template v-if="categories.length === 0" v-for="i in 16" :key="`cat-sk-${i}`">
            <CategorySkeleton />
        </template>
    </div>


    <RowDivider />
</template>



