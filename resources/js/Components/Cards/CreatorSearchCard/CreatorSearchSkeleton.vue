<template>



    <ConsistentContentHolder class="flex flex-row gap-x-6 h-48 w-full my-auto  dark:border-zinc-900 bg-zinc-50 p-5">

        <div class="h-full aspect-square rounded-full overflow-hidden  ">
            <skeleton-div/>
        </div>
        <div class="flex flex-col gap-y-3 flex-grow">
            <div class="flex flex-row gap-x-4">
                <div class="w-64 rounded-xl h-8 overflow-hidden">
                    <skeleton-div/>
                </div>
            </div>
            <div class="w-full rounded-xl h-6 overflow-hidden">
                <skeleton-div/>
            </div>
            <div class="w-3/4 rounded-xl h-6 overflow-hidden">
                <skeleton-div/>
            </div>

        </div>


    </ConsistentContentHolder>



</template>

<script>
import SubscribeButton from "@/Components/Buttons/SubscribeButton.vue";
import ConsistentContentHolder from "@/Components/General/ConsistentContentHolder.vue";
import Badge from "@/Components/General/Badge.vue";
import SkeletonDiv from "@/Components/General/SkeletonDiv.vue";

export default {
name: "CreatorSearchSkeleton",
components: {SkeletonDiv, Badge, ConsistentContentHolder, SubscribeButton},
props: {
creator: {
    type: Object,
    default: null
}
}
}
</script>
