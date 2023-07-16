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
import {onMounted, ref} from "vue";

const name = 'List of subscriptions page';
const channels = ref([]);

// on mounted grab subscriptions using axios and ziggy
onMounted(() => {
    axios.get(route('feed.channels.data')).then((response) => {
        channels.value = response.data.subscriptions.data;
    });
});
</script>



<template>
    <Head>
        <title>Channels</title>
    </Head>

    <div class="mx-5 md:mx-6 lg:mx-16 xl:mx-32 flex flex-col gap-y-5 mt-10">

        <creator-search-card v-if="channels.length > 0" v-for="channel in channels" :creator="channel" :key="channel.id"></creator-search-card>

    </div>




</template>
