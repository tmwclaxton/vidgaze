
<script setup>
import ConsistentPadding from "@/Layouts/Partials/ConsistentPadding.vue";
import CreatorSearchCard from "@/Components/Cards/CreatorSearchCard/CreatorSearchCard.vue";
import {onMounted, ref, watch} from "vue";
import SelectInput from "@/Components/Inputs/SelectInput.vue";
import ErrorMessage from "@/Components/Errors/ErrorMessage.vue";

const name = 'List of subscriptions page';
const channels = ref([]);
const loaded = ref(false);

// on mounted grab subscriptions using axios and ziggy
onMounted(() => {
    axios.get(route('api.feed.channels', {category: category.value}
    )).then((response) => {
        channels.value = response.data.subscriptions.data;
        loaded.value = true;
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
    axios.get(route('api.feed.channels', {category: value}
    )).then((response) => {
        channels.value = response.data.subscriptions.data;
    });
});

</script>



<template>
    <SeoHead
        title="Subscription channels"
        description="Manage and browse the channels you follow on VidGaze."
    />
    <ConsistentPadding class="px-6">
        <div class="mx-0 md:mx-6 lg:mx-16 xl:mx-32 flex flex-col gap-y-5 ">
            <SelectInput class=" ml-auto w-40"
                         :modelValue="'default'"
                         v-model="category" @update:model-value="value => category = value" :options="categoryOptions" :title="'Order By'"/>
            <creator-search-card v-if="channels.length > 0" v-for="channel in channels" :creator="channel" :key="channel.id"></creator-search-card>
        </div>

        <div class="mt-20" v-if="loaded && channels.length === 0">
            <ErrorMessage :message="'Whoops you have no subscriptions'"/>
        </div>
    </ConsistentPadding>
</template>
