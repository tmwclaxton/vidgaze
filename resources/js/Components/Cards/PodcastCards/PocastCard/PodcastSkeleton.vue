<template>
    <div class="w-full h-full flex flex-col gap-3 ">
        <div class="rounded overflow-hidden   w-full aspect-square ">
            <SkeletonDiv/>
        </div>
        <div class="flex flex-row gap-2 ">
            <div class="rounded overflow-hidden  flex-grow h-7 my-auto w-3/4 ">
                <SkeletonDiv/>
            </div>
            <div class="w-1/4 "/>
        </div>
    </div>
</template>

<script setup>
import SkeletonDiv from "@/Components/General/SkeletonDiv.vue";

const name = 'CategorySkeleton';
</script>
