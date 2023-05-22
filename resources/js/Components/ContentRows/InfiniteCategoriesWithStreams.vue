<template>
    <div v-for="categoryWithStreams in categoriesWithStreams">
        <div v-if="categoryWithStreams.streams.data.length !== 0">
            <div class="flex flex-row gap-2  my-4 mb-8 ">
                <!--<StreamIcon class="w-6 h-6 my-auto"/>-->
                <font-awesome-icon class="w-6 h-6 my-auto" :icon="['fas', 'gamepad']" />

                <a :href="route('category.show',{slug:categoryWithStreams.category.slug})"  class="font-bold text-2xl select-none" v-text="categoryWithStreams.category.name"/>
            </div>

            <div class=" grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 ld:grid-cols-3 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                <template v-for="(stream, index) in categoryWithStreams.streams.data" :key="stream.id">
                    <VideoStreamCard :item="stream" :category_page="true"/>
                </template>
                <!--<template v-if="categoryWithStreams.streams.data.length === 0" v-for="i in 6">-->
                <!--    <VideoStreamSkeleton />-->
                <!--</template>-->
            </div>

            <RowDivider />
        </div>
    </div>
</template>

<script setup>
import VideoStreamCard from "@/Components/Cards/VideoStreamCard/VideoStreamCard.vue";
import VideoStreamSkeleton from "@/Components/Cards/VideoStreamCard/VideoStreamSkeleton.vue";
import RowDivider from "@/Components/ContentRows/Partials/RowDivider.vue";

const name = 'InfiniteCategoriesWithStreams';
const props = defineProps({
    categoriesWithStreams: {
        type: Array,
        required: true
    }
});
</script>

<style scoped>

</style>
