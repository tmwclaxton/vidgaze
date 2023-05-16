
<script setup>
import { Head } from '@inertiajs/vue3';
import PaddingLayout from "@/Layouts/PaddingLayout.vue";
import Carousel from "@/Pages/Viewer/Streams/Partials/Carousel.vue";
import {onMounted, ref} from "vue";
import TopStreamsRow from "@/Pages/Viewer/Home/TopStreamsRow.vue";

const categories = ref();
const fetchCategories = () =>  {
    axios.get(route('categories.index'),  {
        params: {
            perPage: 16,
        } } )
        .then(response => {
            setTimeout(() => {
                categories.value = response.data;
            }, 500); // 500ms delay
        })
        .catch(error => {
            console.log(error);
        });
};

onMounted(async () => {
    await fetchCategories();
});





</script>
<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
export default {
    layout: AuthenticatedLayout,
};
</script>
<template>

    <Head title="Popular Streams" />

    <!--<div class="w-full grid grid-cols-1 sm:grid-cols-2">-->
    <!--    <div class="flex flex-col">-->
    <!--        <div id="player" class="bg-black h-96">-->
    <!--            &lt;!&ndash; Add a placeholder for the Twitch embed &ndash;&gt;-->
    <!--        </div>-->
    <!--        <div class="h-52 font-bold text-4xl p-5">MonsterCat</div>-->
    <!--    </div>-->
    <!--    <div class="h-52"></div>-->
    <!--</div>-->
    <PaddingLayout class="-mt-4">
        <!--<Carousel></Carousel>-->

        <TopStreamsRow/>

        <div class="hid den md: flex flex-row gap-2  my-4 mb-8 ">
            <font-awesome-icon :icon="['fas', 'fire']"  class="my-auto h-6"/>
            <p class="font-bold text-2xl select-none">Categories  we think you will like</p>
        </div>
        <div class="grid grid-cols-4 xs:grid-cols-4 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-8 gap-4 w-full">
           <div v-for="category in categories" v-if="categories != null">
                <div  class='w-full flex flex-col gap-1 group'>
                    <a :href="route('category.show',{slug:category.slug})" class="cursor-pointer">
                        <!-- Image -->
                        <div >
                            <img :src="category.thumbnail_url"
                                 class="w-full rounded-xl hover:translate-x-1 hover:-translate-y-1 delay-50 duration-100" />
                        </div>
                        <!-- Games Title -->
                        <p class=" text-base font-bold mt-2 mb-1" v-text="category.name"/>
                    </a>
                    <!-- Category Tags -->
                    <div class="flex flex-row flex-wrap gap-2">
                        <div v-for="tag in category.tags" class="cursor-pointer px-3 p-1 rounded-full text-xs font-bold bg-zinc-200 dark:bg-zinc-700">
                            <p v-text="tag"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PaddingLayout>

</template>
