<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import DashboardItem from "@/Pages/Studio/Dashboard/Partials/DashboardItem.vue";
import {ref} from "vue";
const name = "LatestItemPerformance"

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
});



</script>

<template>
    <ConsistentContentHolder class="p-5 h-full">

        <p class="font-bold text-lg">Latest Video Performance</p>


        <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

        <DashboardItem v-for="item in items" :key="item.id" :item="item" />

    </ConsistentContentHolder>
</template>

