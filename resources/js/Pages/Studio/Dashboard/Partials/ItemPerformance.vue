<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import DashboardItem from "@/Pages/Studio/Dashboard/Partials/DashboardItem.vue";
import {ref} from "vue";
const name = "LatestItemPerformance"

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    item_analytics: {
        type: Object,
        required: true
    }
});



</script>

<template>
    <ConsistentContentHolder class="p-5 h-max">

        <p class="font-bold text-lg">Latest Video Performance</p>


        <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

        <DashboardItem :key="item.id" :item="item" :item_analytics="item_analytics"></DashboardItem>

    </ConsistentContentHolder>
</template>

