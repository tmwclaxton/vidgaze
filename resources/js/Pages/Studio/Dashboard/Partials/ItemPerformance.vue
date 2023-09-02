<template>
    <ConsistentContentHolder class="p-5 h-full">

        <p class="font-bold text-lg">Latest Video Performance</p>


        <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

        <div class="w-full grid grid-cols-3">
            <div class="cols-1">
                <img src="https://picsum.photos/1600/900" class="w-full aspect-[21/12] rounded-lg shadow">
            </div>
            <div class="col-2">

            </div>
        </div>

    </ConsistentContentHolder>
</template>

<script setup>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
const name = "LatestItemPerformance"
</script>

<style scoped>

</style>
