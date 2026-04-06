<template>
    <div class="w-full h-full rounded overflow-hidden">
        <div class="w-full aspect-square rounded overflow-hidden">
            <SkeletonDiv shimmer />
        </div>
        <div class="p-2 space-y-2.5">
            <div class="h-4 w-full rounded-md overflow-hidden">
                <SkeletonDiv shimmer />
            </div>
            <div class="h-4 w-[85%] rounded-md overflow-hidden">
                <SkeletonDiv shimmer />
            </div>
        </div>
    </div>
</template>

<script>
import SkeletonDiv from "@/Components/General/SkeletonDiv.vue";

export default {
    name: "PodcastCardSkeleton",
    components: { SkeletonDiv },
};
</script>
