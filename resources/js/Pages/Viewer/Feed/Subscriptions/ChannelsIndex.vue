<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PaddingLayout from "@/Layouts/Partials/ConsistentPadding.vue";
export default {
    components: {PaddingLayout},
    layout: AuthenticatedLayout,

};
</script>
<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import CreatorSearchCard from "@/Components/Cards/CreatorSearchCard/CreatorSearchCard.vue";
import {onMounted, ref, watch} from "vue";
import SelectInput from "@/Components/Inputs/SelectInput.vue";

const name = 'List of subscriptions page';
const channels = ref([]);

// on mounted grab subscriptions using axios and ziggy
onMounted(() => {
    axios.get(route('feed.channels.data', {category: category.value}
    )).then((response) => {
        channels.value = response.data.subscriptions.data;
    });
});

const categoryOptions = [
    {value: 'default', label: 'Default'},
    {value: 'az', label: 'A-Z'},
    {value: 'za', label: 'Z-A'},
    {value: 'newest', label: 'Newest'},
    {value: 'oldest', label: 'Oldest'},
];
const category = ref('default');

// watch category and sort channels
watch(category, (value) => {
    axios.get(route('feed.channels.data', {category: value}
    )).then((response) => {
        channels.value = response.data.subscriptions.data;
    });
});

</script>



<template>
    <Head>
        <title>Channels</title>
    </Head>
    <ConsistentPadding class="px-6">


        <div class="mx-0 md:mx-6 lg:mx-16 xl:mx-32 flex flex-col gap-y-5 mt-10">
            <SelectInput class=" ml-auto w-40"
                         :modelValue="'default'"
                         v-model="category" @update:model-value="value => category = value" :options="categoryOptions" :title="'Order By'"/>

            <creator-search-card v-if="channels.length > 0" v-for="channel in channels" :creator="channel" :key="channel.id"></creator-search-card>

        </div>

    </ConsistentPadding>


</template>
