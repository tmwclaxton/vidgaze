<template>
    <div class="relative flex flex-row w-full gap-0">
        <div class="w-48 lg:w-96 aspect-[21/12] shrink-0 overflow-hidden rounded-md bg-zinc-100 dark:bg-zinc-800/80">
            <SkeletonDiv shimmer />
        </div>
        <div class="pl-4 sm:pl-5 flex-1 min-w-0 flex flex-col gap-3 pt-0.5">
            <div class="h-5 sm:h-6 lg:h-8 rounded-lg overflow-hidden w-full max-w-3xl">
                <SkeletonDiv shimmer />
            </div>
            <div class="h-5 sm:h-6 rounded-lg overflow-hidden w-[92%] max-w-2xl lg:hidden">
                <SkeletonDiv shimmer />
            </div>
            <div class="flex flex-row gap-2 pt-0.5 items-start">
                <div class="w-9 aspect-square rounded-full overflow-hidden shrink-0 bg-zinc-100 dark:bg-zinc-800/80">
                    <SkeletonDiv shimmer />
                </div>
                <div class="flex flex-col gap-2 flex-1 min-w-0 justify-center">
                    <div class="h-5 rounded-md overflow-hidden w-36 max-w-[70%]">
                        <SkeletonDiv shimmer />
                    </div>
                    <div class="h-4 rounded-md overflow-hidden w-24 max-w-[50%] lg:hidden">
                        <SkeletonDiv shimmer />
                    </div>
                    <div class="hidden lg:block h-20 rounded-lg overflow-hidden w-full mt-1">
                        <SkeletonDiv shimmer />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import SkeletonDiv from "@/Components/General/SkeletonDiv.vue";

export default {
    name: "VideoStreamSearchSkeleton",
    components: { SkeletonDiv },
};
</script>
