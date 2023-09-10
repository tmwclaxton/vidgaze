
<script setup>

import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import StudioComment from "@/Pages/Studio/Dashboard/Partials/StudioComment.vue";

const name = 'LatestComments';

const props = defineProps({
    comments: {
        type: Object,
        required: true
    },
});
</script>


<template>
    <ConsistentContentHolder class="p-5 h-full">

        <p class="font-bold text-lg">Latest Comments</p>

        <div class="border-t border-zinc-200 dark:border-zinc-600 my-2 mb-3"></div>

        <div class="flex flex-col gap-y-3">
            <StudioComment v-for="comment in comments" :key="comment.id" :comment="comment"/>

        </div>
    </ConsistentContentHolder>
</template>
