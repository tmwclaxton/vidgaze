<template>
    <ConsistentContentHolder
        class="relative h-full w-full my-auto overflow-hidden dark:border-zinc-900 bg-zinc-50 dark:bg-zinc-900/50"
    >
        <div class="pointer-events-none flex flex-row w-full px-2 sm:px-8 py-5 items-center gap-4 sm:gap-5">
            <div class="min-w-16 w-24 shrink-0 aspect-square rounded-full overflow-hidden">
                <SkeletonDiv shimmer />
            </div>
            <div class="flex flex-col flex-1 gap-3 min-w-0">
                <div class="flex flex-row gap-3 sm:gap-4 items-center flex-wrap">
                    <div class="h-8 rounded-lg overflow-hidden flex-1 min-w-[8rem] max-w-md">
                        <SkeletonDiv shimmer />
                    </div>
                    <div class="h-9 w-[7.5rem] rounded-full overflow-hidden shrink-0 hidden sm:block">
                        <SkeletonDiv shimmer />
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="j in 3"
                        :key="j"
                        class="h-6 w-14 sm:w-16 rounded-md overflow-hidden"
                    >
                        <SkeletonDiv shimmer />
                    </div>
                </div>
            </div>
        </div>
    </ConsistentContentHolder>
</template>

<script>
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import SkeletonDiv from "@/Components/General/SkeletonDiv.vue";

export default {
    name: "CreatorSearchSkeleton",
    components: { SkeletonDiv, ConsistentContentHolder },
};
</script>
